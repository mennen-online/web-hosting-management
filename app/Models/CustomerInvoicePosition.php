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
        'unitName',
        'unitPrice',
        'discountPercentage'
    ];

    protected $casts = [
        'unitPrice' => 'json'
    ];

    public function invoice() {
        return $this->belongsTo(CustomerInvoice::class);
    }

}
