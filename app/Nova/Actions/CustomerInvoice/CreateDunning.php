<?php

namespace App\Nova\Actions\CustomerInvoice;

use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Endpoints\DunningEndpoint;
use App\Services\Lexoffice\Lexoffice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CreateDunning extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $dunningEndpoint = app()->make(DunningEndpoint::class);

        $models->each(function (CustomerInvoice $customerInvoice) use ($dunningEndpoint) {
            $dunningInfo = $dunningEndpoint->create($customerInvoice);

            $dunning = $dunningEndpoint->get(new CustomerInvoice(['lexoffice_id' => $dunningInfo->id]));

            Lexoffice::storeCustomerInvoice($dunning, $customerInvoice->customer, false);
        });
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
