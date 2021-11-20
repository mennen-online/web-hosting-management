<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Exceptions\LexofficeException;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Lexoffice\Connector;

class InvoicesEndpoint extends Connector
{
    public function get(CustomerInvoice $customerInvoice) {
        $response = $this->getRequest('/invoices/' . $customerInvoice->lexoffice_id);

        if($response->ok()) {
            return $response->object();
        }
    }

    public function getRedirect(CustomerInvoice $customerInvoice) {
        return 'https://app.lexoffice.de/permalink/invoices/view/' . $customerInvoice->lexoffice_id;
    }

    public function create(CustomerProduct $customerProduct) {
        $data = [
            'voucherDate' => now()->toIso8601ZuluString(),
            'address' => $this->buildAddress($customerProduct),
            'lineItems' => $this->buildLineItems($customerProduct),
            'totalPrice' => $this->buildTotalPrice(),
            'taxConditions' => $this->buildTaxConditions(),
            'shippingConditions' => $this->buildShippingConditions()
        ];

        $response = $this->postRequest('/invoices?finalize=true', $data);

        $customerProduct->customer->invoices()->create(['lexoffice_id' => $response->id]);
    }

    private function buildAddress(CustomerProduct $customerProduct) {
        if($customerProduct->customer->lexoffice_id !== null) {
            return [
                'contactId' => $customerProduct->customer->lexoffice_id
            ];
        }

        throw new LexofficeException('Customer '. $customerProduct->customer->user->email .' not exists in Lexoffice');
    }

    private function buildLineItems(CustomerProduct $customerProduct) {
        $product = $customerProduct->product;

        $domain = $customerProduct->domain;

        $inwx = app()->make(DomainObject::class);

        $domainPrice = $inwx->getPrice($domain->name);

        return [
            [
                'type' => 'custom',
                'name' => $domain->name,
                'quantity' => 1,
                'unitName' => 'Jahr',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'netAmount' => (double)$domainPrice['price'],
                    'taxRatePercentage' => 19
                ]
            ], [
                'type' => 'custom',
                'name' => $product->name,
                'quantity' => 1,
                'unitName' => 'Jahr',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'netAmount' => $product->price,
                    'taxRatePercentage' => 19
                ]
            ]
        ];
    }

    private function buildTotalPrice() {
        return [
            'currency' => 'EUR'
        ];
    }

    private function buildTaxConditions() {
        return [
            'taxType' => 'net'
        ];
    }

    private function buildShippingConditions() {
        return [
            'shippingType' => 'serviceperiod',
            'shippingDate' => now()->toIso8601ZuluString(),
            'shippingEndDate' => now()->addYear()->toIso8601ZuluString()
        ];
    }
}
