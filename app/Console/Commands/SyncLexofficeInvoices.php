<?php

namespace App\Console\Commands;

use App\Exceptions\LexofficeException;
use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyncLexofficeInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lexoffice:invoices:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Invoices with Lexoffice';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected InvoicesEndpoint $invoicesEndpoint,
        protected VoucherlistEndpoint $voucherlistEndpoint,
        protected Collection $invoices,
        protected array $processedCustomerIds = []
    ) {
        parent::__construct();

        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

        $this->invoicesEndpoint = app()->make(InvoicesEndpoint::class);

        $this->invoices = collect();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->voucherlistEndpoint->isLexofficeAvailable()) {
            $this->withProgressBar(Customer::orderBy('number')->get(), function ($customer) {
                if (!in_array($customer->id, $this->processedCustomerIds)) {
                    try {
                        $this->processImportCustomer($customer);
                    } catch (LexofficeException $lexofficeException) {
                        sleep(60);
                        $this->processImportCustomer($customer);
                    }
                }
            });
        }
        return 0;
    }

    private function processImportCustomer($customer)
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

            $this->processedCustomerIds[] = $customer->id;
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
