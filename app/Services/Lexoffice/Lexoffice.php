<?php

namespace App\Services\Lexoffice;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Lexoffice
{
    public static function getNewInvoiceNumbersByCustomer(Customer $customer, bool $reSync = false)
    {
        $voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

        $voucherlistEndpoint->setContactId($customer->lexoffice_id);

        foreach ([
                     VoucherlistEndpoint::VOUCHER_STATUS_OPEN.
                     ','.VoucherlistEndpoint::VOUCHER_STATUS_PAID.
                     ','.VoucherlistEndpoint::VOUCHER_STATUS_PAIDOFF.
                     ','.VoucherlistEndpoint::VOUCHER_STATUS_VOIDED,
                     VoucherlistEndpoint::VOUCHER_STATUS_OVERDUE,
                 ] as $voucherStatus) {
            $voucherlistEndpoint->setVoucherType('invoice');
            $voucherlistEndpoint->setVoucherStatus($voucherStatus);
            $voucherlistEndpoint->setPageSize(250);
            $page = 0;

            do {
                $result = $voucherlistEndpoint->setPage($page)->index();
                if ($result) {
                    collect($result->content)->filter(function ($invoice) use ($customer, $reSync) {
                        if (!$reSync && !$customer->invoices()->where('lexoffice_id', $invoice->id)->exists()
                            || $reSync) {
                            $invoiceData = app()->make(InvoicesEndpoint::class)->get(new CustomerInvoice([
                                'lexoffice_id' => $invoice->id
                            ]));

                            DB::transaction(function () use ($invoiceData, $customer, $reSync) {
                                $invoice = self::convertLexofficeInvoiceToCustomerInvoice($invoiceData);
                                $customerInvoice = $customer->invoices()->updateOrCreate(
                                    [
                                        'lexoffice_id' => $invoice['lexoffice_id']
                                    ],
                                    $invoice
                                );

                                if ($reSync) {
                                    $customerInvoice->position()->delete();
                                }

                                $customerInvoice->position()->createMany(
                                    self::convertLexofficeInvoiceLineItemToCustomerInvoicePosition(
                                        $invoiceData->lineItems
                                    )
                                );
                            });
                        }
                    });
                }
                $page += 1;
            } while ($result && !$result->last);
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

    private static function convertLexofficeInvoiceToCustomerInvoice(object $invoice)
    {
        return [
            'lexoffice_id'          => $invoice->id,
            'voucher_number'        => $invoice->voucherNumber,
            'voucher_date'          => $invoice->voucherDate,
            'total_net_amount'      => $invoice->totalPrice->totalNetAmount,
            'total_gross_amount'    => $invoice->totalPrice->totalGrossAmount,
            'total_tax_amount'      => $invoice->totalPrice->totalTaxAmount,
            'payment_term_duration' => $invoice->paymentConditions->paymentTermDuration ?? 0
        ];
    }

    private static function convertLexofficeInvoiceLineItemToCustomerInvoicePosition(array $lineItems)
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
}
