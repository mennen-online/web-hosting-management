<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CustomerContact extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\CustomerContact::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'first_name', 'last_name', 'email'
    ];

    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request) {
        return [
            BelongsTo::make(__('Customer'))->searchable(),
            ID::make(__('ID'), 'id')->sortable(),
            Select::make(__('Salutation'))->options([
                ''       => 'Keine Anrede',
                'Herr'   => 'Herr',
                'Frau'   => 'Frau',
                'Divers' => 'Divers'
            ]),
            Text::make(__('First Name'), 'first_name'),
            Text::make(__('Last Name'), 'last_name'),
            Text::make(__('Email'), 'email'),
            Text::make(__('Phone'), 'phone')
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public
    function cards(Request $request) {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public
    function filters(Request $request) {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public
    function lenses(Request $request) {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public
    function actions(Request $request) {
        return [];
    }

    public
    function title() {
        return $this->salutation.' '.$this->first_name.' '.$this->last_name;
    }
}
