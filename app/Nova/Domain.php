<?php

namespace App\Nova;

use App\Nova\Actions\Domains\CheckAndRegisterDomain;
use App\Nova\Actions\UpdateDomainDNS;
use App\Services\Internetworx\Objects\DomainObject;
use Armincms\Fields\Chain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

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
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    /**
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            BelongsTo::make(__('Domain Product'), 'domainProduct'),
            BelongsTo::make(
                __('Customer Product'),
                'customerProduct'
            )->showOnCreating(false)->showOnUpdating(false)->showOnDetail(true),
            ID::make(__('Registrar ID'))->readonly(true),
            Text::make(__('Name'), 'name')->readonly(true)
                ->showOnCreating(false),
            Chain::as(
                'domainname',
                function () {
                    return [
                        Text::make(__('Name'), 'name')->readonly($this->resource->exists),
                    ];
                }
            ),
            Chain::with(
                'domainname',
                function ($request) {
                    $domain = $request->input('name');
                    $domainProducts = \App\Models\DomainProduct::all();
                    if ($domainProducts->count() === 0) {
                        Artisan::call('internetworx:domains:price:sync');
                        $domainProducts = \App\Models\DomainProduct::all();
                    }
                    /**
                     * @phpstan-ignore-next-line
                     */
                    $tlds = $domainProducts->pluck('tld')->map(
                        function ($tld) {
                            return '.' . $tld;
                        }
                    );
                    if (Str::endsWith($domain, $tlds->toArray())) {
                        $domainObject = app()->make(DomainObject::class);
                        $status = $domainObject->check($domain)[0];
                        $price = $domainObject->getPrice($domain)[$status['domain']];

                        return [
                            Currency::make(
                                __('Price'),
                                function () use ($price) {
                                    return number_format($price['price'], 2) * 1.25;
                                }
                            )->currency($price['currency'])->readonly(true),
                            Text::make(
                                __('Price Information'),
                                function () {
                                    return "Preis / Jahr zzgl. 19 % MwSt.";
                                }
                            )->readonly(true),
                            Boolean::make(__('Price Confirmed'))
                                ->rules(['required', 'accepted'])->withMeta(['value' => 0])
                        ];
                    }
                },
                'domain-price-confirmation'
            ),
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
        return [
            new CheckAndRegisterDomain(),
            new UpdateDomainDNS()
        ];
    }
}
