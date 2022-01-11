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
        'primary',
        'email',
        'phone'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'primary' => 'boolean'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
}
