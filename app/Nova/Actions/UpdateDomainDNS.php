<?php

namespace App\Nova\Actions;

use App\Models\Server;
use App\Services\Internetworx\Objects\NameserverObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Select;

class UpdateDomainDNS extends Action
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
        $dnsObject = app()->make(NameserverObject::class);
        $server = Server::find($fields->get('server_id'));
        foreach($models as $model) {
            $dnsObject->create($model, $server);
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make(__('Server'), 'server_id')->options(Server::all()->map(function(Server $server) {
                return [
                    $server->id => $server->name
                ];
            }))->displayUsingLabels()
        ];
    }
}
