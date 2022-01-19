<?php

namespace App\Models;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'lexoffice_id',
        'user_id',
        'number',
        'company',
        'salutation',
        'first_name',
        'last_name',
        'email',
        'phone'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'company' => 'json'
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(
            function (Customer $customer) {
                /**
                 * @phpstan-ignore-next-line
                 */
                if ($customer->customer_type) {
                    /**
                     * @phpstan-ignore-next-line
                     */
                    $customer->lexoffice_id = match ($customer->customer_type) {
                        'company' => app()->make(ContactsEndpoint::class)->createCompanyContact($customer)->id,
                        'person' => app()->make(ContactsEndpoint::class)->createPersonContact($customer)->id,
                    };
                }

                if ($customer->customer_type === 'company') {
                    $customer->company = [
                        /**
                         * @phpstan-ignore-next-line
                         */
                        'allowTaxFreeInvoices' => $customer->allowTaxFreeInvoices,
                        /**
                         * @phpstan-ignore-next-line
                         */
                        'name' => $customer->companyName,
                        /**
                         * @phpstan-ignore-next-line
                         */
                        'taxNumber' => $customer->taxNumber,
                        /**
                         * @phpstan-ignore-next-line
                         */
                        'vatRegistrationId' => $customer->vatRegistrationId
                    ];
                }

                if (isset($customer->customer_type)) {
                    $fillableFields = $customer->getFillable();
                    foreach ($customer->attributes as $attribute => $value) {
                        if (!in_array($attribute, $fillableFields)) {
                            unset($customer->attributes[$attribute]);
                        }
                    }
                }
            }
        );

        self::updating(
            function (Customer $customer) {
                if (!empty($customer->street_number)
                    && !empty($customer->postcode)
                    && !empty($customer->city)
                    && !empty($customer->countryCode)) {
                    app()->make(ContactsEndpoint::class)->createOrUpdateCompanyBillingAddress(
                        $customer,
                        /**
                        * @phpstan-ignore-next-line
                        */
                        $customer->supplement ?? '',
                        $customer->street_number,
                        $customer->postcode,
                        $customer->city,
                        $customer->countryCode
                    );
                }

                $fillableFields = $customer->getFillable();
                foreach ($customer->attributes as $attribute => $value) {
                    if (!in_array($attribute, $fillableFields)) {
                        unset($customer->attributes[$attribute]);
                    }
                }
            }
        );
    }

    /**
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address()
    {
        return $this->hasOne(CustomerAddress::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(CustomerProduct::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    public function tasks() {
        return $this->morphMany(Task::class, 'taskable');
    }
}
