<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
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

    public function customer() {
        return $this->belongsToMany(Customer::class);
    }
}
