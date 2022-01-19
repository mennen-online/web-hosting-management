<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoicePosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_invoice_id',
        'type',
        'name',
        'description',
        'unit_name',
        'unit_price',
        'currency',
        'net_amount',
        'tax_rate_percentage',
        'discount_percentage'
    ];

    protected $casts = [
        'unitPrice' => 'json'
    ];

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
    }

    public function tasks() {
        return $this->morphMany(Task::class, 'taskable');
    }
}
