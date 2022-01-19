<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @property \App\Models\DomainProduct $resource
 */
class DomainProduct extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\DomainProduct::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'tld';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'tld'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('TLD'))->sortable(),
            Text::make(__('Period')),
            Currency::make(__('Registration Price'), 'reg_price')->currency($this->resource->currency),
            Currency::make(__('Renewal Price'), 'renewal_price')->currency($this->resource->currency),
            Currency::make(__('Update Price'), 'update_price')->currency($this->resource->currency),
            Currency::make(__('Restore Price'), 'restore_price')->currency($this->resource->currency),
            Currency::make(__('Transfer Price'), 'transfer_price')->currency($this->resource->currency),
            Currency::make(__('Whois Protection Price'), 'whois_protection_price')->currency($this->resource->currency)
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
