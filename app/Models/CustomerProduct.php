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

        self::creating(function(CustomerProduct $customerProduct) {
            if($customerProduct->product_id === null) {
                $domain = Domain::find($customerProduct->domain_id);

                $domainName = explode('.', $domain->name);
                if (count($domainName) > 2) {
                    $tld = $domainName[1].'.'.$domainName[2];
                } else {
                    $tld = $domainName[1];
                }

                $product = Product::where('name', $tld)->first();

                CustomerProduct::create([
                    'customer_id' => $customerProduct->customer_id,
                    'product_id'  => $product->id,
                    'domain_id'   => $customerProduct->domain_id
                ]);
            }
        });
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
