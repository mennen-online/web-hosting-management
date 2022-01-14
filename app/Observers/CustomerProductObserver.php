<?php

namespace App\Observers;

use App\Models\CustomerProduct;
use App\Models\Domain;
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
        if($customerProduct->domain->exists && !app()->runningUnitTests()) {
            $product = $customerProduct->product;

            if (class_exists($classname = 'App\\Services\\Product\\Models\\'.Str::kebab($product->name))) {
                new $classname();
            }

            $domain = $customerProduct->domain;

            $domainInfo = app()->make(DomainObject::class)->create($domain);

            $domain->update(['registrar_id' => $domainInfo['roId']]);

            $domain->refresh();

            if($domain->registrar_id === null) {
                $domainInfo = app()->make(DomainObject::class)->get($domain);
                $domain->update(['registrar_id' => $domainInfo['roId']]);
            }

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
        $customerProduct->domain->delete();
    }

    /**
     * Handle the CustomerProduct "restored" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function restored(CustomerProduct $customerProduct)
    {
        $customerProduct->domain->restore();
    }

    /**
     * Handle the CustomerProduct "force deleted" event.
     *
     * @param  \App\Models\CustomerProduct  $customerProduct
     * @return void
     */
    public function forceDeleted(CustomerProduct $customerProduct)
    {
        $customerProduct->domain->forceDelete();
    }
}
