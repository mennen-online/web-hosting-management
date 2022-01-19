<?php

namespace App\Nova;

use App\Nova\Actions\CustomerInvoice\CreateDunning;
use App\Nova\Actions\Invoices\DownloadInvoice;
use App\Nova\Actions\Invoices\OpenInLexoffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\ActionRequest;

/**
 * @property \App\Models\CustomerInvoice $resource
 */
class CustomerInvoice extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\CustomerInvoice::class;

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
        'voucher_number', 'voucher_date'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        $fields = [
            BelongsTo::make(__('Customer')),
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Rechnungsnummer', 'voucher_number')->readonly(true),
            Date::make('Rechnungsdatum', 'voucher_date')->readonly(true),
            Currency::make('Gesamtbetrag inkl. MwSt.', 'total_gross_amount')->currency('EUR')->readonly(true),
            Number::make(
                'Positionen',
                function () {
                    return $this->resource->position()->count();
                }
            )->readonly(true)->showOnDetail(false),
            HasMany::make('Customer Invoice Position', 'position')
        ];
        $fields[] = Date::make(
            'Zahlungsziel (fällig am)',
            function () {
                return Carbon::parse($this->resource->voucher_date)
                    ->addDays($this->resource->payment_term_duration)
                    ->format('d.m.Y');
            }
        )->readonly(true);

        return $fields;
    }

    public function getTotalPrice()
    {
        $total = 0.00;

        $total = $this->resource->position()->each(
            function ($position) use ($total) {
                return $total += $position->net_amount - ($position->net_amount / 100 * $position->discount_percentage);
            }
        );

        return $total;
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
            new OpenInLexoffice(),
            new DownloadInvoice(),
            (new CreateDunning())->canSee(function ($request) {
                if ($request instanceof ActionRequest) {
                    return true;
                }

                return $this->resource instanceof \App\Models\CustomerInvoice
                    && Carbon::parse($this->resource->voucher_date)
                        ->addDays($this->resource->payment_term_duration)->isBefore(now());
            })
        ];
    }
}
