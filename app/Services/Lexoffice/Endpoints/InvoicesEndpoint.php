<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Exceptions\LexofficeException;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Lexoffice\Connector;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoicesEndpoint extends Connector
{
    public function get(CustomerInvoice $customerInvoice)
    {
        return $this->getRequest('/invoices/' . $customerInvoice->lexoffice_id);
    }

    public function renderInvoice(CustomerInvoice $customerInvoice)
    {
        $this->acceptableStatusCodes[] = 406;

        $response = $this->getRequest('/invoices/' . $customerInvoice->lexoffice_id . '/document');

        if (property_exists($response, 'documentFileId')) {
            Log::info('Lexoffice ID: ' . $customerInvoice->lexoffice_id . ' DFID: ' . $response->documentFileId);
            return app()->make(FilesEndpoint::class)->get($response->documentFileId);
        }
    }

    public function getRedirect(CustomerInvoice $customerInvoice)
    {
        return 'https://app.lexoffice.de/permalink/invoices/view/' . $customerInvoice->lexoffice_id;
    }

    public function create(CustomerProduct $customerProduct)
    {
        $data = [
            'voucherDate' => $this->buildLexofficeDate(now()),
            'address' => $this->buildAddress($customerProduct),
            'lineItems' => $this->buildLineItems($customerProduct),
            'totalPrice' => $this->buildTotalPrice(),
            'taxConditions' => $this->buildTaxConditions(),
            'shippingConditions' => $this->buildShippingConditions()
        ];

        return $this->postRequest('/invoices?finalize=true', $data);
    }

    private function buildAddress(CustomerProduct $customerProduct)
    {
        if ($customerProduct->customer->lexoffice_id !== null) {
            return [
                'contactId' => $customerProduct->customer->lexoffice_id
            ];
        }

        throw new LexofficeException(
            'Customer ' . $customerProduct->customer->user->email . ' not exists in Lexoffice'
        );
    }

    private function buildLineItems(CustomerProduct $customerProduct)
    {
        $product = $customerProduct->product;

        $domain = $customerProduct->domain;

        $inwx = app()->make(DomainObject::class);

        $domainPrice = $inwx->getPrice($domain->name);

        return [
            [
                'type' => 'custom',
                'name' => $domain->name,
                'description' => 'Domain',
                'quantity' => 1,
                'unitName' => 'Jahr',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'netAmount' => number_format($domainPrice->first()['price'], 2),
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

    private function buildTotalPrice()
    {
        return [
            'currency' => 'EUR'
        ];
    }

    private function buildTaxConditions()
    {
        return [
            'taxType' => 'net'
        ];
    }

    private function buildShippingConditions()
    {
        return [
            'shippingType' => 'serviceperiod',
            'shippingDate' => $this->buildLexofficeDate(now()),
            'shippingEndDate' => $this->buildLexofficeDate(now()->addYear())
        ];
    }

    private function buildLexofficeDate(Carbon $carbon)
    {
        $date = date('c', strtotime($carbon->format('Y-m-d\TH:i:s.vO')));

        $milliseconds = Str::substr($carbon->format('v'), 0, 3);

        return Str::replace('+', '.' . $milliseconds . '+', $date);
    }
}
