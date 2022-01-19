<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Connector;

class DunningEndpoint extends Connector
{
    public function get(CustomerInvoice $customerInvoice)
    {
        return $this->getRequest('/dunnings/'.$customerInvoice->lexoffice_id);
    }

    public function create(CustomerInvoice $customerInvoice)
    {
        $invoiceData = app()->make(InvoicesEndpoint::class)->get($customerInvoice);

        $data = [
            'voucherDate' => now()->toIso8601String(),
            'address' => [
                'contactId' => $customerInvoice->customer->lexoffice_id
            ],
            'lineItems' => $customerInvoice->position()->get()->map(function ($position, $index) use ($invoiceData) {
                return [
                    'type' => $position->type,
                    'name' => $position->name,
                    'quantity' => $invoiceData->lineItems[$index]->quantity,
                    'unitName' => $position->unit_name,
                    'unitPrice' => [
                        'currency' => 'EUR',
                        'netAmount' => $position->net_amount,
                        'taxRatePercentage' => $position->tax_rate_percentage
                    ],
                    'discountPercentage' => $position->discount_percentage
                ];
            })->toArray(),
            'taxConditions' => $invoiceData->taxConditions,
            'shippingConditions' => $invoiceData->shippingConditions
        ];

        return $this->postRequest('/dunnings?precedingSalesVoucherId=' . $customerInvoice->lexoffice_id, $data);
    }
}
