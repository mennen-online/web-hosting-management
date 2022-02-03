<?php

namespace App\Services\Lexoffice;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Endpoints\DunningEndpoint;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class Lexoffice
{
    public static function getNewInvoiceNumbersByCustomer(Customer $customer, bool $reSync = false)
    {
        $voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

        $voucherlistEndpoint->setContactId($customer->lexoffice_id);

        $voucherlistEndpoint->setPageSize(250);

        foreach ([
                     VoucherlistEndpoint::VOUCHER_STATUS_NORMAL,
                     VoucherlistEndpoint::VOUCHER_STATUS_OVERDUE,
                 ] as $voucherStatus) {
            $voucherlistEndpoint->setVoucherType('invoice');
            $voucherlistEndpoint->setVoucherStatus($voucherStatus);
            $page = 0;

            do {
                $result = self::loadInvoices($voucherlistEndpoint, $page, $customer, $reSync);
                $page += 1;
            } while (property_exists($result, 'last') && !$result->last);
        }
    }

    public static function importInvoices(Customer $customer, Collection $invoices)
    {
        $customerInvoices = $invoices->map(function ($invoice) {
            return self::convertLexofficeInvoiceToCustomerInvoice($invoice);
        });

        $customerInvoicePositions = $invoices->map(function ($invoice) {
            return self::convertLexofficeInvoiceLineItemToCustomerInvoicePosition($invoice->lineItems);
        });

        DB::beginTransaction();

        $customer->invoices()->createMany($customerInvoices->toArray())->each(function (
            $invoice,
            $index
        ) use (
            $customerInvoicePositions
        ) {
            $invoice->position()->createMany($customerInvoicePositions[$index]);
        });

        DB::commit();
    }

    public static function convertLexofficeInvoiceToCustomerInvoice(object $invoice, string $type = 'invoice')
    {
        return [
            'type'                  => $type,
            'lexoffice_id'          => $invoice->id,
            'voucher_number'        => $invoice->voucherNumber ?? '',
            'voucher_date'          => $invoice->voucherDate,
            'total_net_amount'      => $invoice->totalPrice->totalNetAmount,
            'total_gross_amount'    => $invoice->totalPrice->totalGrossAmount,
            'total_tax_amount'      => $invoice->totalPrice->totalTaxAmount,
            'payment_term_duration' => $invoice->paymentConditions->paymentTermDuration ?? 0
        ];
    }

    public static function convertLexofficeInvoiceLineItemToCustomerInvoicePosition(array $lineItems)
    {
        $positions = [];

        foreach ($lineItems as $lineItem) {
            $data = match ($lineItem->type) {
                'custom' => [
                    'type'                => $lineItem->type,
                    'name'                => $lineItem->name,
                    'unit_name'           => $lineItem->unitName ?? null,
                    'currency'            => $lineItem->unitPrice->currency,
                    'net_amount'          => $lineItem->unitPrice->netAmount,
                    'tax_rate_percentage' => $lineItem->unitPrice->taxRatePercentage,
                    'discount_percentage' => $lineItem->discountPercentage
                ],
                'text'   => [
                    'type'        => $lineItem->type,
                    'name'        => $lineItem->name,
                    'description' => $lineItem->description
                ],
                default  => []
            };

            if (!empty($data)) {
                $positions[] = $data;
            }
        }

        return $positions;
    }

    /**
     * @param object $invoiceData
     * @param Customer $customer
     * @param bool|null $reSync
     * @return void
     */
    public static function storeCustomerInvoice(
        object $invoiceData,
        Customer $customer,
        string $type = 'invoice',
        ?bool $reSync = false
    ): void {
        DB::transaction(function () use ($invoiceData, $customer, $reSync, $type) {
            $invoice = self::convertLexofficeInvoiceToCustomerInvoice($invoiceData);
            $customerInvoice = $customer->invoices()->updateOrCreate([
                'lexoffice_id' => $invoice['lexoffice_id'],
                'type'         => $type,
            ], $invoice);

            if ($reSync) {
                $customerInvoice->position()->delete();
            }

            $customerInvoice->position()->createMany(
                self::convertLexofficeInvoiceLineItemToCustomerInvoicePosition($invoiceData->lineItems)
            );
        });
    }

    public static function buildLexofficeDate(?Carbon $carbon = null)
    {
        $date = date('c', strtotime($carbon->format('Y-m-d\TH:i:s.vO')));

        $milliseconds = Str::substr($carbon->format('v'), 0, 3);

        return Str::replace('+', '.' . $milliseconds . '+', $date);
    }

    public static function addDunningPositionToCustomerInvoice(object $lexofficeInvoiceData)
    {
        $lineItems = $lexofficeInvoiceData->lineItems;

        $lineItem = new stdClass();
        $lineItem->type = 'custom';
        $lineItem->name = 'Mahngebühren';
        $lineItem->quantity = 1;
        $lineItem->unitName = 'Stück';
        $lineItem->unitPrice = new stdClass();
        $lineItem->unitPrice->currency = 'EUR';
        $lineItem->unitPrice->netAmount = 3;
        $lineItem->unitPrice->taxRatePercentage = 19.0;
        $lineItem->discountPercentage = 0;
        $lineItem->lineItemAmount = 3.0;

        $lineItems[] = $lineItem;

        $totalNet = 0.00;

        foreach ($lineItems as $lineItem) {
            $totalNet += $lineItem->unitPrice->netAmount;
        }

        $lexofficeInvoiceData->lineItems = $lineItems;

        $lexofficeInvoiceData->totalPrice->totalNetAmount = $totalNet;

        $lexofficeInvoiceData->totalPrice->totalGrossAmount = $totalNet * 1.19;

        $lexofficeInvoiceData->totalPrice->totalTaxAmount = $totalNet * 0.19;

        $lexofficeInvoiceData->taxAmounts[0]->netAmount = $totalNet;

        $lexofficeInvoiceData->taxAmounts[0]->taxAmount = $totalNet * 0.19;

        return $lexofficeInvoiceData;
    }

    /**
     * @param VoucherlistEndpoint $voucherlistEndpoint
     * @param int $page
     * @param Customer $customer
     * @param bool $reSync
     * @return object
     */
    private static function loadInvoices(
        VoucherlistEndpoint $voucherlistEndpoint,
        int $page,
        Customer $customer,
        bool $reSync
    ): object {
        $result = $voucherlistEndpoint->setPage($page)->index();
        if ($result) {
            collect($result->content)->filter(function ($invoice) use ($customer, $reSync) {
                if (!$reSync && !$customer->invoices()->where('lexoffice_id', $invoice->id)->exists() || $reSync) {
                    $invoiceData = app()->make(InvoicesEndpoint::class)->get(new CustomerInvoice([
                        'lexoffice_id' => $invoice->id
                    ]));

                    self::storeCustomerInvoice($invoiceData, $customer, reSync: $reSync);

                    self::loadDunningForInvoice($invoiceData, $customer, $reSync);
                }
            });
        }
        return $result;
    }

    /**
     * @param object $invoiceData
     * @param Customer $customer
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private static function loadDunningForInvoice(object $invoiceData, Customer $customer, bool $reSync): void
    {
        if (property_exists($invoiceData, 'relatedVouchers')) {
            collect($invoiceData->relatedVouchers)->filter(function ($voucher) {
                if ($voucher->voucherType === 'dun') {
                    return $voucher;
                }
            })->each(function ($dunning) use ($customer, $reSync) {
                $dunningData = app()->make(DunningEndpoint::class)->get($dunning->id);

                self::storeCustomerInvoice($dunningData, $customer, 'dunning', $reSync);
            });
        }
    }
}
