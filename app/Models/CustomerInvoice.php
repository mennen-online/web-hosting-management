<?php

namespace App\Models;

use Cassandra\Custom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lexoffice_id',
        'customer_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function products() {
        return $this->hasMany(CustomerProduct::class, 'customer_id', 'customer_id');
    }
}
