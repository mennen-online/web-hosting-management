<?php

namespace App\Console\Commands;

use App\Exceptions\LexofficeException;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    protected Collection $invoices;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);

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
                $this->processImportCustomer($customer);
            });
        }
        return 0;
    }

    private function processImportCustomer($customer)
    {
        $invoiceNumbers = collect();

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
                        $invoiceNumbers->push(collect($result->content)->filter(function ($invoice) use ($customer) {
                            if (Str::startsWith(
                                $invoice->voucherNumber,
                                'RE'
                            ) && !$customer->invoices()->where('lexoffice_id', $invoice->id)->exists()) {
                                return $invoice->id;
                            }
                        }));
                    }
                    $page += 1;
                } while ($result && $result->last === false);
            }
        }

        $invoiceNumbers->each(function ($invoiceNumber) use ($customer) {
            $this->processInvoice($customer, $invoiceNumber);
        });
    }

    private function processInvoice(Customer $customer, string $invoice)
    {
        try {
            $invoiceData = app()->make(InvoicesEndpoint::class)
                ->get(new CustomerInvoice(['lexoffice_id' => $invoice]));

            $invoice = $customer->invoices()->firstOrCreate([
                'lexoffice_id'          => $invoiceData->id,
                'voucher_number'        => $invoiceData->voucherNumber,
                'voucher_date'          => $invoiceData->voucherDate,
                'total_net_amount'      => $invoiceData->totalPrice->totalNetAmount,
                'total_gross_amount'    => $invoiceData->totalPrice->totalGrossAmount,
                'total_tax_amount'      => $invoiceData->totalPrice->totalTaxAmount,
                'payment_term_duration' => $invoiceData->paymentConditions->paymentTermDuration
            ]);

            if ($invoice->position()->count() !== count($invoiceData->lineItems)) {
                $invoice->position()->each(function ($position) {
                    $position->delete();
                });

                collect($invoiceData->lineItems)->each(function ($position) use ($invoice) {
                    if (Str::is(['custom', 'text'], $position->type)) {
                        $invoiceData = match ($position->type) {
                            'custom' => [
                                'type'                => $position->type,
                                'name'                => $position->name,
                                'unit_name'           => $position->unitName ?? null,
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
                        };

                        if (!empty($invoiceData)) {
                            $invoice->position()->create($invoiceData);
                        }
                    }
                });
            }
        } catch (LexofficeException $lexofficeException) {
            Log::error($lexofficeException->getMessage());
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }
}
