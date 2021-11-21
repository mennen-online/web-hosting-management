<?php

namespace App\Observers;

use App\Models\CustomerProduct;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use Illuminate\Support\Str;
use Log;

class CustomerProductObserver
{
    /**
     * Handle the CustomerProduct "created" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function created(CustomerProduct $customerProduct)
    {
        if($customerProduct->domain) {
            $product = $customerProduct->product;

            if (class_exists($classname = 'App\\Services\\Product\\Models\\'.Str::kebab($product->name))) {
                new $classname();
            }

            $domain = $customerProduct->domain;

            app()->make(DomainObject::class)->create($domain);

            $invoice = app()->make(InvoicesEndpoint::class)->create($customerProduct);

            $customerProduct->customer->invoices()->create(['lexoffice_id' => $invoice->id]);
        }
    }

    /**
     * Handle the CustomerProduct "updated" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function updated(CustomerProduct $customerProduct)
    {
        //
    }

    /**
     * Handle the CustomerProduct "deleted" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function deleted(CustomerProduct $customerProduct)
    {
        //
    }

    /**
     * Handle the CustomerProduct "restored" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function restored(CustomerProduct $customerProduct)
    {
        //
    }

    /**
     * Handle the CustomerProduct "force deleted" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function forceDeleted(CustomerProduct $customerProduct)
    {
        //
    }
}
