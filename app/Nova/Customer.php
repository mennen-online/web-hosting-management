<?php

namespace App\Nova;

use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Armincms\Fields\Chain;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
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
        'id',
    ];

    //public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request) {
        $customer = null;
        if ($this->lexoffice_id !== null) {
            $customer = app()->make(ContactsEndpoint::class)->get($this->lexoffice_id);
        }

        $fields = [
            BelongsTo::make(__('User'))->withoutTrashed(),
            ID::make(__('ID'))->readonly(true)->showOnIndex(false),
            Text::make(__('Lexoffice ID'))->readonly(true)->showOnIndex(false),
            Text::make(__('Kundennummer'), 'customer_number', function() use($customer) {
                return $customer->roles->customer->number;
            })->showOnCreating(false)->showOnUpdating(is_object($customer) && property_exists($customer, 'roles'))->readonly(true),
            new Panel(__('Billing'), function() use($customer){
                if($customer !== null) {
                    return [
                        Text::make(__('Street & Number'), 'street_number', function () use ($customer) {
                            return property_exists($customer, 'addresses') ? $customer->addresses->billing[0]->street : '';
                        })->readonly($this->addressCanBeUpdated($customer)),
                        Text::make(__('Supplement'), 'supplement', function () use ($customer) {
                            return property_exists($customer, 'addresses') ? $customer->addresses->billing[0]->supplement : '';
                        })->readonly($this->addressCanBeUpdated($customer)),
                        Text::make(__('Postcode'), 'postcode', function () use ($customer) {
                            return property_exists($customer, 'addresses') ? $customer->addresses->billing[0]->zip : '';
                        })->readonly($this->addressCanBeUpdated($customer)),
                        Text::make(__('City'), 'city', function () use ($customer) {
                            return property_exists($customer, 'addresses') ? $customer->addresses->billing[0]->city : '';
                        })->readonly($this->addressCanBeUpdated($customer)),
                        Select::make(__('Country'), 'countryCode', function () use ($customer) {
                            return property_exists($customer, 'addresses') ? $customer->addresses->billing[0]->countryCode : '';
                        })->options([
                            'DE' => 'Deutschland',
                            'IT' => 'Italien',
                            'FR' => 'Frankreich'
                        ])->readonly($this->addressCanBeUpdated($customer))
                    ];
                }
            }),
            HasMany::make(__('Customer Contact'), 'contacts'),
            HasMany::make(__('Customer Invoices'), 'invoices'),
            Chain::as('customer_type', function () {
                return [
                    Select::make(__('Art des Kunden'), 'customer_type')->options([
                        'company' => 'Unternehmen',
                        'person'  => 'Privatperson'
                    ])
                ];
            })->showOnUpdating(false)->showOnCreating(true),
            Chain::with('customer_type', function ($request) {
                return match ($request->input('customer_type')) {
                    'company' => [
                        Text::make(__('Name des Unternehmens'), 'company.name'),
                        Text::make(__('Steuernummer'), 'taxNumber'),
                        Text::make(__('Umsatzsteuer ID'), 'vatRegistrationId'),
                        HasMany::make(__('Contact Persons'), 'contacts', CustomerContact::class)
                    ],
                    'person' => [
                        Select::make(__('Anrede'), 'salutation')->options([
                            'Herr' => 'Herr',
                            'Frau' => 'Frau'
                        ]),
                        Text::make(__('Vorname'), 'firstName'),
                        Text::make(__('Nachname'), 'lastName'),
                        Trix::make(__('Notizen'), 'note')
                    ],
                    null => []
                };
            })->showOnUpdating(false)->showOnCreating(true)
        ];

        return $fields;
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

    private function addressCanBeUpdated(object $customer) {
        return (property_exists($customer, 'addresses') && property_exists($customer->addresses, 'billing') && count($customer->addresses->billing) > 1);
    }
}
