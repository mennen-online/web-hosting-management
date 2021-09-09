<?php

namespace App\Nova;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Arr;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Customer extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Customer::class;

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

    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request) {
        $company = json_decode($this->resource->company);
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make(__('Kundennummer'), function () {
                return app()->make(ContactsEndpoint::class)->get($this->resource->lexoffice_id)->roles->customer->number;
            })->readonly(true),
            Text::make(__('Unternehmen'), function () use ($company) {
                return $company ? $company->name : '';
            })->readonly(true),
            Text::make(__('Anrede'), function () {
                return $this->resource->contacts()->first()->salutation;
            })->readonly(true),
            Text::make(__('Vorname'), function () {
                return $this->resource->contacts()->first()->first_name;
            })->readonly(true),
            Text::make(__('Nachname'), function () {
                return $this->resource->contacts()->first()->last_name;
            })->readonly(true),
            HasMany::make('Contact Persons', 'contacts', CustomerContact::class),
            HasMany::make('Customer Invoices', 'invoices', CustomerInvoice::class)
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request) {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request) {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request) {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request) {
        return [];
    }
}
