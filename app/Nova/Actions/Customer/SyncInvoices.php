<?php

namespace App\Nova\Actions\Customer;

use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class SyncInvoices extends Action
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        protected VoucherlistEndpoint $voucherlistEndpoint,
        protected InvoicesEndpoint $invoicesEndpoint
    ) {
        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

        $this->invoicesEndpoint = app()->make(InvoicesEndpoint::class);
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(fn($customer) => $this->syncInvoices($customer));

        return Action::message("Invoices Synchronised successful");
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }

    private function syncInvoices($customer)
    {
        $invoicesPerCustomer = collect();

        $page = 0;

        foreach ([
                     'open',
                     'overdue',
                     'paid',
                     'paidoff',
                     'voided',
                     'transferred',
                     'sepadebit'
                 ] as $voucherStatus) {
            $this->voucherlistEndpoint->setVoucherType('invoice');
            $this->voucherlistEndpoint->setContactId($customer->lexoffice_id);
            $this->voucherlistEndpoint->setVoucherStatus($voucherStatus);

            $this->voucherlistEndpoint->setPageSize(250);

            do {
                $result = $this->voucherlistEndpoint->setPage($page)->index();
                if ($result) {
                    $invoicesPerCustomer->push($result->content);
                }
                $page += 1;
            } while ($result && $result->last === false);
        }
        $invoiceNumbers = $invoicesPerCustomer->flatten()->filter(function ($invoice) use ($customer) {
            if (!$customer->invoices()->where('lexoffice_id', $invoice->id)->exists()) {
                return $invoice->id;
            }
        });

        if ($invoiceNumbers->count() > 0) {
            $invoicesData = retry(5, function () use ($invoiceNumbers) {
                return $this->getInvoices($invoiceNumbers);
            });

            $invoices = $invoicesData->map(function ($invoiceData) {
                return $this->convertLexofficeInvoiceToInvoiceModelData($invoiceData);
            });

            $lineItems = $invoicesData->map(function ($invoiceData) {
                return $this->convertLexofficeInvoiceLineItemToModelData($invoiceData->lineItems);
            });

            DB::beginTransaction();

            $customer->invoices()->createMany($invoices->toArray())->each(function (
                $invoice,
                $index
            ) use (
                $lineItems
            ) {
                $invoice->position()->createMany($lineItems[$index]);
            });

            DB::commit();
        }
    }

    private function convertLexofficeInvoiceToInvoiceModelData(object $invoice): array
    {
        return [
            'lexoffice_id'          => $invoice->id,
            'voucher_number'        => $invoice->voucherNumber,
            'voucher_date'          => $invoice->voucherDate,
            'total_net_amount'      => $invoice->totalPrice->totalNetAmount,
            'total_gross_amount'    => $invoice->totalPrice->totalGrossAmount,
            'total_tax_amount'      => $invoice->totalPrice->totalTaxAmount,
            'payment_term_duration' => $invoice->paymentConditions->paymentTermDuration
        ];
    }

    private function convertLexofficeInvoiceLineItemToModelData(array $lineItems): array
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
     * @param Collection $invoiceNumbers
     * @return Collection
     */
    private function getInvoices(Collection $invoiceNumbers): Collection
    {
        $invoicesData = $this->invoicesEndpoint->getAll($invoiceNumbers->pluck('id'));
        return $invoicesData;
    }
}
