<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'lexoffice_id',
        'user_id',
        'company'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'company' => 'json'
    ];

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
