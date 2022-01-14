<?php

namespace App\Nova\Actions\Invoices;

use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DownloadInvoice extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $showOnDetail = true;

    public $showOnIndex = true;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($models->count() === 1) {
            $invoice = $models->first();
            $lexofficeInvoices = app()->make(InvoicesEndpoint::class);

            $invoiceInformation = $lexofficeInvoices->renderInvoice($invoice);

            return Action::download($invoiceInformation->path, $invoice->lexoffice_id . '.pdf');
        }
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
}
