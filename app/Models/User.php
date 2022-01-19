<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 *
 * @property Customer $customer
 * @property Collection $roles
 * @property Collection $customerContacts
 * @property Collection $customerInvoices
 * @property Collection $customerProducts
 * @property string $name
 */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, MustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    /**
     * @return HasOne
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    /**
     * @return HasManyThrough
     */
    public function customerContacts()
    {
        return $this->hasManyThrough(CustomerContact::class, Customer::class);
    }

    /**
     * @return HasManyThrough
     */
    public function customerInvoices()
    {
        return $this->hasManyThrough(CustomerInvoice::class, Customer::class);
    }

    /**
     * @return HasManyThrough
     */
    public function customerProducts()
    {
        return $this->hasManyThrough(CustomerProduct::class, Customer::class);
    }

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function tasks() {
        return $this->hasMany(Task::class);
    }
}
