<?php

namespace App\Console\Commands;

use App\Exceptions\LexofficeException;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

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

    protected VoucherlistEndpoint $voucherlistEndpoint;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->voucherlistEndpoint->isLexofficeAvailable()) {
            $this->withProgressBar(Customer::all(), function ($customer) {
                $this->processImportCustomer($customer);
            });
        }
        return 0;
    }

    private function processImportCustomer($customer)
    {
        $customerInvoices = collect();
        foreach ([
                     'open',
                     'overdue',
                     'paid',
                     'paidoff',
                     'voided',
                     'transferred',
                     'sepadebit'
                 ] as $voucherStatus) {
            foreach ([
                         'invoice',
                         'creditnote'
                     ] as $voucherType) {
                $this->voucherlistEndpoint->setVoucherType($voucherType);
                $this->voucherlistEndpoint->setContactId($customer->lexoffice_id);
                $this->voucherlistEndpoint->setVoucherStatus($voucherStatus);

                $this->voucherlistEndpoint->setPageSize(250);

                $page = 0;

                do {
                    $result = $this->voucherlistEndpoint->setPage($page)->index();
                    if ($result) {
                        foreach ($result->content as $invoice) {
                            $customerInvoices->add($invoice);
                        }
                    }

                    $page += 1;
                } while ($result && $result->last === false);
            }
        }

        $customerInvoices->each(function ($invoice) use ($customer) {
            $this->processInvoice($customer, $invoice);
        });
    }

    private function processInvoice($customer, $invoice)
    {
        $this->info('Processing Invoice ' . $invoice->voucherNumber);
        try {
            $invoiceData = app()->make(InvoicesEndpoint::class)->get(new CustomerInvoice(['lexoffice_id' => $invoice->id]));

            if($customer->invoices()->where('lexoffice_id', $invoice->id)->exists() === null) {
                $invoice = $customer->invoices()->create([
                    'lexoffice_id'          => $invoice->id,
                    'voucher_number'        => $invoice->voucherNumber,
                    'voucher_date'          => $invoice->voucherDate,
                    'total_net_amount'      => $invoiceData->totalPrice->totalNetAmount,
                    'total_gross_amount'    => $invoiceData->totalPrice->totalGrossAmount,
                    'total_tax_amount'      => $invoiceData->totalPrice->totalTaxAmount,
                    'payment_term_duration' => $invoiceData->paymentConditions->paymentTermDuration
                ]);

                collect($invoiceData->lineItems)->each(function ($position) use ($invoice) {
                    if (Str::is(['custom', 'text'], $position->type)) {
                        $invoice->position()->create(match ($position->type) {
                            'custom' => [
                                'type'                => $position->type,
                                'name'                => $position->name,
                                'unit_name'           => $position->unitName ?? "",
                                'currency'            => $position->unitPrice->currency,
                                'net_amount'          => $position->unitPrice->netAmount,
                                'tax_rate_percentage' => $position->unitPrice->taxRatePercentage,
                                'discount_percentage' => $position->discountPercentage
                            ],
                            'text'   => [
                                'type'        => $position->type,
                                'name'        => $position->name,
                                'description' => $position->description
                            ],
                            default  => []
                        });
                    }
                });
            }
        }catch(LexofficeException $lexofficeException) {
            Log::error($lexofficeException->getMessage());
        }
    }
}
