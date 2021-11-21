<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'registrar_id',
        'name'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected static function boot() {
        parent::boot();

        self::creating(function(Domain $domain) {
            $customer = Customer::where('user_id', $domain->user_id)->first();

            $domainName = explode('.', $domain->name);
            if(count($domainName) > 2) {
                $tld = $domainName[1].'.'.$domainName[2];
            }else {
                $tld = $domainName[1];
            }

            $product = Product::where('name', $tld)->first();

            $customerProduct = $customer->products()->create([
                'product_id' => $product->id
            ]);

            Session::put($domain->name . '_customer-product', [
                'domain' => $domain->name,
                'user_id' => $domain->user_id,
                'customer_product_id' => $customerProduct->id
            ]);

            unset($domain->user_id, $domain->ComputedField, $domain->price_confirmed);
        });


    }

    public function user() {
        return $this->customer()->first()->user();
    }

    public function customer() {
        return $this->hasOneThrough( Customer::class, CustomerProduct::class,
            'domain_id', 'id', 'id', 'customer_id');
    }

    public function customerProduct() {
        return $this->hasOne(CustomerProduct::class);
    }
}
