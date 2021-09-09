<?php

namespace App\Nova;

use App\Services\Forge\Endpoints\ServersEndpoint;
use Laravel\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;

class Server extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Server::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $serversEndpoint = app()->make(ServersEndpoint::class);

        $server = $serversEndpoint->get($this->resource);

        if($server !== null) {
            $server = $server->server;
        }

        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Name'), function() use($server) {
                return $server->name;
            }),
            Text::make(__('IP Address'), function() use($server) {
                return $server->ip_address;
            }),
            Trix::make(__('PHP Versions'), function() use($server) {
                $output = "";

                foreach($server->php_versions as $php_version) {
                    $output .= $php_version->displayable_version . '<br/>';
                }

                return $output;
            })
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
