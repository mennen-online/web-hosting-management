<?php

namespace App\Nova;

use App\Services\Internetworx\Objects\DomainObject;
use Armincms\Fields\Chain;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Str;

class Domain extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Domain::class;

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
        return [
            ID::make(__('ID'), 'id')->sortable(),
            BelongsTo::make(__('User'))->searchable(true),
            Chain::as('domainname', function() {
                return [
                    Text::make(__('Name'), 'name')->readonly($this->resource->exists),
                ];
            }),
            Chain::with('domainname', function($request) {
                $domain = $request->input('name');
                $tlds = \App\Models\Product::where('description', 'LIKE', '%Domain%')->get()->pluck('name')->map(function($tld) {
                    return '.'.$tld;
                });
                if(Str::endsWith($domain, $tlds->toArray())) {
                    $domainObject = app()->make(DomainObject::class);
                    $status = $domainObject->check($domain)['resData']['domain'][0];
                    $price = $domainObject->getPrice($domain)['resData']['domain'][$status['domain']];

                    return [
                        Currency::make(__('Price'), function() use($status, $price) {
                            return number_format($price['price'], 2);
                        })->currency($price['currency'])->readonly(true),
                        Text::make(__('Price Information'), function() {
                            return "Preis / Jahr zzgl. 19 % MwSt.";
                        })->readonly(true),
                        Boolean::make(__('Price Confirmed'))
                        ->rules(['required', 'accepted'])->withMeta(['value' => 0])
                    ];
                }
            }, 'domain-price-confirmation'),
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
