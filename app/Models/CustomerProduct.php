<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Domain $domain
 * @property Product $product
 * @property Server $server
 * @property Customer $customer
 */
class CustomerProduct extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'customer_id',
        'product_id',
        'server_id',
        'domain_id'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(
            function (CustomerProduct $customerProduct) {
                if ($customerProduct->product_id === null && !app()->runningInConsole()) {
                    $domain = Domain::find($customerProduct->domain_id);

                    $domainName = explode('.', $domain->name);
                    if (count($domainName) > 2) {
                        $tld = $domainName[1] . '.' . $domainName[2];
                    } else {
                        $tld = $domainName[1];
                    }

                    $product = Product::where('name', $tld)->first();

                    CustomerProduct::create(
                        [
                            'customer_id' => $customerProduct->customer_id,
                            'product_id' => $product->id,
                            'domain_id' => $customerProduct->domain_id
                        ]
                    );
                }
            }
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoices()
    {
        return $this->belongsToMany(CustomerInvoice::class, 'customer_id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }
  
    public function domainProduct()
    {
        return $this->hasOneThrough(
            DomainProduct::class,
            Domain::class,
            'id',
            'id',
            'domain_id',
            'domain_product_id'
        );
    }
}
