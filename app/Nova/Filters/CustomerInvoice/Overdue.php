<?php

namespace App\Nova\Filters\CustomerInvoice;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\BooleanFilter;
use Laravel\Nova\Filters\Filter;

class Overdue extends BooleanFilter
{
    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $ids = $value['overdue'] ? $query->get()->filter(function($customerInvoice){
            $value = Carbon::now();
            $dueDate = Carbon::parse($customerInvoice->voucher_date)->addDays($customerInvoice->payment_term_duration);
            if(!$value->isBefore($dueDate)) {
                return $customerInvoice;
            }
        })->pluck('id') : collect();

        return $ids->count() ? $query->whereIn('id', $ids->toArray()) : $query;
    }

    public function options(Request $request)
    {
        return [
            'Overdue' => 'overdue'
        ];
    }
}
