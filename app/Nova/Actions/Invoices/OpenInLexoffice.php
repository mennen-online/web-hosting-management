<?php

namespace App\Nova\Actions\Invoices;

use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class OpenInLexoffice extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $showOnIndex = false;

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $invoicesEndpoint = app()->make(InvoicesEndpoint::class)->getRedirect($models->first());

        return Action::openInNewTab($invoicesEndpoint);
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
