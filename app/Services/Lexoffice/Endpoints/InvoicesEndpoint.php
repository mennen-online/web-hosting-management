<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Exceptions\LexofficeException;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Lexoffice\Connector;
use App\Services\Lexoffice\Lexoffice;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoicesEndpoint extends Connector
{
    public function get(CustomerInvoice $customerInvoice)
    {
        return $this->getRequest('/invoices/' . $customerInvoice->lexoffice_id);
    }

    public function getAll(Collection $collection)
    {
        return retry(5, function () use ($collection) {
            return collect($this->getAllRequest('/invoices/', $collection->toArray()))->map(function ($response) {
                return $response->object();
            });
        }, 60000);
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

    public function create(CustomerProduct $customerProduct, ?Carbon $voucherDate = null)
    {
        $data = [
            'voucherDate' => Lexoffice::buildLexofficeDate($voucherDate ?? now()),
            'address' => $this->buildAddress($customerProduct),
            'lineItems' => $this->buildLineItems($customerProduct),
            'totalPrice' => $this->buildTotalPrice(),
            'taxConditions' => $this->buildTaxConditions(),
            'shippingConditions' => $this->buildShippingConditions()
        ];

        return $this->postRequest('/invoices?finalize=true', $data);
    }

    public function buildAddress(CustomerProduct $customerProduct)
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

    public function buildLineItems(CustomerProduct $customerProduct)
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

    public function buildTotalPrice()
    {
        return [
            'currency' => 'EUR'
        ];
    }

    public function buildTaxConditions()
    {
        return [
            'taxType' => 'net'
        ];
    }

    public function buildShippingConditions()
    {
        return [
            'shippingType'    => 'serviceperiod',
            'shippingDate'    => Lexoffice::buildLexofficeDate(now()),
            'shippingEndDate' => Lexoffice::buildLexofficeDate(now()->addYear())
        ];
    }
}
