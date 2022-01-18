<?php

namespace App\Nova\Actions\Customer;

use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use App\Services\Lexoffice\Lexoffice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncInvoices extends Action
{
    use InteractsWithQueue, Queueable;

    protected VoucherlistEndpoint $voucherlistEndpoint;

    protected InvoicesEndpoint $invoicesEndpoint;

    public function __construct()
    {
        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

        $this->invoicesEndpoint = app()->make(InvoicesEndpoint::class);
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(fn($customer) => $this->syncInvoices($customer));

        return Action::message("Invoices Synchronised successful");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    private function syncInvoices($customer)
    {
        $invoiceNumbers = Lexoffice::getNewInvoiceNumbersByCustomer($customer);

        if ($invoiceNumbers->count() > 0) {
            Lexoffice::importInvoices($customer, $invoiceNumbers);
        }
    }
}
