<?php

namespace App\Nova;

use App\Nova\Actions\Invoices\OpenInLexoffice;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

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
        'id',
    ];

    public static $displayInNavigation = false;

    protected InvoicesEndpoint $invoicesEndpoint;

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request) {
        $this->invoicesEndpoint = app()->make(InvoicesEndpoint::class);

        $invoice = $this->invoicesEndpoint->get($this->resource);

        if ($invoice === null) {
            Log::error('Invoice cannot be found in Lexoffice', $this->resource->toArray());
            return [];
        }

        $fields = [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('Rechnungsnummer', function () use ($invoice) {
                return $invoice->voucherNumber;
            })->readonly(true),
            Date::make('Rechnungsdatum', function () use ($invoice) {
                return Carbon::parse($invoice->voucherDate)->format('d.m.Y');
            })->readonly(true),
            Currency::make('Gesamtbetrag inkl. MwSt.', function () use ($invoice) {
                return $this->getTotalPrice($invoice);
            })->currency($invoice->totalPrice->currency)->readonly(true),
            Number::make('Positionen', function () use ($invoice) {
                return count($invoice->lineItems);
            })->readonly(true)->showOnDetail(false),
        ];

        $lineItems = $this->lineItemFields($invoice->lineItems);

        foreach($lineItems as $position => $lineItem) {
            $fields[] = (new Panel(__('Position ' . $position + 1), $lineItem));
        }

        $fields[] = Date::make('Zahlungsziel (fällig am)', function () use ($invoice) {
            return Carbon::parse($invoice->voucherDate)->addDays($invoice->paymentConditions->paymentTermDuration)->format('d.m.Y');
        })->readonly(true);

        return $fields;
    }

    public function getTotalPrice(object $invoice) {
        $total = 0.00;

        if(property_exists($invoice, 'lineItems')) {
            foreach($invoice->lineItems as $lineItem) {
                if(property_exists($lineItem, 'lineItemAmount')) {
                    $total += number_format($lineItem->lineItemAmount + ($lineItem->lineItemAmount / 100 * $lineItem->unitPrice->taxRatePercentage), 2);
                }
            }
        }

        return $total;
    }

    public function lineItemFields(array $lineItems) {
        return collect($lineItems)->map(function ($lineItem, $position) {
            return [
                Text::make(__('Produkt'), function () use ($lineItem) {
                    return $lineItem->name;
                })->showOnIndex(false),
                Text::make(__('Anzahl'), function () use ($lineItem) {
                    return $lineItem->quantity.' '.$lineItem->unitName;
                })->showOnIndex(false),
                Currency::make(__('Preis / Stück (Netto)'), function () use ($lineItem) {
                    return $lineItem->unitPrice->netAmount;
                })->currency($lineItem->unitPrice->currency)->showOnIndex(false),
                Currency::make(__('Preis / Stück (Brutto)'), function () use ($lineItem) {
                    return $lineItem->unitPrice->grossAmount;
                })->currency($lineItem->unitPrice->currency)->showOnIndex(false),
                Currency::make(__('Rabatt in Euro (Netto)'), function() use($lineItem) {
                    return $lineItem->discountPercentage ?? 0;
                })->currency($lineItem->unitPrice->currency)->showOnIndex(false)
            ];
        });
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
        return [
            new OpenInLexoffice()
        ];
    }
}
