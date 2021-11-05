<?php

namespace App\Observers;

use App\Models\CustomerProduct;
use App\Services\Internetworx\Objects\DomainObject;

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
        $domain = $customerProduct->domain;
        app()->make(DomainObject::class)->create($domain);
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
