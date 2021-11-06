<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;

class CustomerProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_id',
        'server_id',
        'domain_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected static function boot() {
        parent::boot();
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function invoices() {
        return $this->belongsToMany(CustomerInvoice::class, 'customer_id', 'customer_id');
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function server() {
        return $this->belongsTo(Server::class);
    }

    public function domain() {
        return $this->belongsTo(Domain::class);
    }
}
