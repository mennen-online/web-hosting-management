<?php

namespace App\Observers;

use App\Models\CustomerInvoice;
use App\Notifications\Customer\InvoiceCreatedNotification;

class CustomerInvoiceObserver
{
    /**
     * Handle the CustomerInvoice "created" event.
     *
     * @param \App\Models\CustomerInvoice $customerInvoice
     * @return void
     */
    public function created(CustomerInvoice $customerInvoice)
    {
        if (!app()->runningInConsole()) {
            $customerInvoice->customer->user->notify(new InvoiceCreatedNotification($customerInvoice));
        }
    }

    /**
     * Handle the CustomerInvoice "updated" event.
     *
     * @param \App\Models\CustomerInvoice $customerInvoice
     * @return void
     */
    public function updated(CustomerInvoice $customerInvoice)
    {
        //
    }

    /**
     * Handle the CustomerInvoice "deleted" event.
     *
     * @param \App\Models\CustomerInvoice $customerInvoice
     * @return void
     */
    public function deleted(CustomerInvoice $customerInvoice)
    {
        //
    }

    /**
     * Handle the CustomerInvoice "restored" event.
     *
     * @param \App\Models\CustomerInvoice $customerInvoice
     * @return void
     */
    public function restored(CustomerInvoice $customerInvoice)
    {
        //
    }

    /**
     * Handle the CustomerInvoice "force deleted" event.
     *
     * @param \App\Models\CustomerInvoice $customerInvoice
     * @return void
     */
    public function forceDeleted(CustomerInvoice $customerInvoice)
    {
        //
    }
}
