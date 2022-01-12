<?php

namespace App\Models;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'lexoffice_id',
        'user_id',
        'company',
        'salutation',
        'first_name',
        'last_name',
        'email',
        'phone'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'company' => 'json'
    ];

    protected static function boot() {
        parent::boot();

        self::creating(function(Customer $customer) {
            if($customer->customer_type) {
                $customer->lexoffice_id = match ($customer->customer_type) {
                    'company' => app()->make(ContactsEndpoint::class)->createCompanyContact($customer)->id,
                    'person' => app()->make(ContactsEndpoint::class)->createPersonContact($customer)->id,
                };
            }

            if($customer->customer_type === 'company') {
                $customer->company = [
                    'allowTaxFreeInvoices' => $customer->allowTaxFreeInvoices,
                    'name'                 => $customer->companyName,
                    'taxNumber'            => $customer->taxNumber,
                    'vatRegistrationId'    => $customer->vatRegistrationId
                ];
            }

            if(isset($customer->customer_type)) {
                $fillableFields = $customer->getFillable();
                foreach ($customer->attributes as $attribute => $value) {
                    if (!in_array($attribute, $fillableFields)) {
                        unset($customer->attributes[$attribute]);
                    }
                }
            }
        });

        self::updating(function(Customer $customer) {
            if(!empty($customer->street_number) && !empty($customer->postcode) && !empty($customer->city) && !empty($customer->countryCode)) {
                app()->make(ContactsEndpoint::class)->createOrUpdateCompanyBillingAddress($customer, $customer->supplement ?? '', $customer->street_number, $customer->postcode, $customer->city, $customer->countryCode);
            }

            $fillableFields = $customer->getFillable();
            foreach($customer->attributes as $attribute => $value) {
                if(!in_array($attribute, $fillableFields)) {
                    unset($customer->attributes[$attribute]);
                }
            }
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function contacts() {
        return $this->hasMany(CustomerContact::class);
    }

    public function products() {
        return $this->hasMany(CustomerProduct::class);
    }

    public function invoices() {
        return $this->hasMany(CustomerInvoice::class);
    }
}
