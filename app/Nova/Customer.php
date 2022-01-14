<?php

namespace App\Nova;

use Armincms\Fields\Chain;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Panel;

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
    public static $title = 'user.name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'first_name', 'last_name', 'email'
    ];

    //public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make(__('User'))->withoutTrashed(),
            ID::make(__('ID'))->readonly(true)->showOnIndex(false),
            Text::make(__('Lexoffice ID'))->readonly(true)->showOnIndex(false),
            Text::make(__('Kundennummer'), 'number')->showOnCreating(false),
            Text::make(__('Straße & Nr.'), 'address.street')->readonly(true),
            Text::make(__('PLZ'), 'address.zip')->readonly(true),
            Text::make(__('Ort'), 'address.city')->readonly(true),
            new Panel(
                __('Billing'),
                function () {
                    return [HasOne::make('CustomerAddress', 'address')];
                }
            ),
            HasMany::make(__('Customer Contact'), 'contacts')->showOnCreating(false),
            HasMany::make(__('Customer Invoices'), 'invoices')->showOnCreating(false),
            HasMany::make(__('Customer Products'), 'products')->showOnCreating(false),
            Chain::as(
                'customer_type',
                function () {
                    return [
                        Select::make(__('Art des Kunden'), 'customer_type')->options(
                            [
                                'company' => 'Unternehmen',
                                'person' => 'Privatperson'
                            ]
                        )
                    ];
                }
            )->showOnUpdating(false)->showOnCreating(true),
            Chain::with(
                'customer_type',
                function ($request) {
                    return match ($request->input('customer_type')) {
                        'company' => [
                            Text::make(__('Name des Unternehmens'), 'company.name'),
                            Text::make(__('Steuernummer'), 'taxNumber'),
                            Text::make(__('Umsatzsteuer ID'), 'vatRegistrationId'),
                            HasMany::make(__('Contact Persons'), 'contacts', CustomerContact::class)
                        ],
                        'person' => [
                            Select::make(__('Anrede'), 'salutation')->options(
                                [
                                    'Herr' => 'Herr',
                                    'Frau' => 'Frau'
                                ]
                            ),
                            Text::make(__('Vorname'), 'firstName'),
                            Text::make(__('Nachname'), 'lastName'),
                            Trix::make(__('Notizen'), 'note')
                        ],
                        default => []
                    };
                }
            )->showOnUpdating(false)->showOnCreating(true)
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
