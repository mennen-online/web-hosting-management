<?php

namespace App\Models;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 * @property-read string $customer_type
 * @property-read boolean $allowTaxFreeInvoices
 * @property-read string $companyName
 * @property-read string $taxNumber
 * @property-read string $vatRegistrationId
 * @property array $attributes
 * @property-read string $supplement
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

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }
}
