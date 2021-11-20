<?php

namespace App\Nova\Actions\Domains;

use App\Models\Domain;
use App\Notifications\Customer\DomainRegistrationSuccessful;
use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class CheckAndRegisterDomain extends Action
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
        $models->each(function(Domain $domain) {
            if($domain->customerProduct !== null) {
                app()->make(DomainObject::class)->create($domain);

                $domain->customerProduct->customer->user->notify(new DomainRegistrationSuccessful($domain));
            }
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
