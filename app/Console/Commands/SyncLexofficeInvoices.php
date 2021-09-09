<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use Illuminate\Console\Command;

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
    public function __construct() {
        parent::__construct();

        $this->voucherlistEndpoint = app()->make(VoucherlistEndpoint::class);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        if ($this->voucherlistEndpoint->isLexofficeAvailable()) {
            $this->withProgressBar(Customer::all(), function ($customer) {
                $this->processImportCustomer($customer);
            });
        }
        return 0;
    }

    private function processImportCustomer($customer) {
        $customerInvoices = collect();
        foreach ([
            'draft',
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
                'creditnote',
                'orderconfirmation'
            ] as $voucherType) {
                $this->voucherlistEndpoint->setVoucherType($voucherType);
                $this->voucherlistEndpoint->setContactId($customer->lexoffice_id);
                $this->voucherlistEndpoint->setVoucherStatus($voucherStatus);

                $this->voucherlistEndpoint->setPageSize(250);

                $page = 0;

                do {
                    $result = $this->voucherlistEndpoint->setPage($page)->index();
                    foreach ($result->content as $invoice) {
                        $customerInvoices->add($invoice);
                    }

                    $page += 1;
                } while ($result->last === false);
            }
        }

        if ($customerInvoices->count() !== $customer->invoices()->count()) {
            $customerInvoices->each(function ($invoice) use ($customer) {
                $this->processInvoice($customer, $invoice);
            });
        } else {
            $this->info($customer->id . ' has no new Invoices');
        }
    }

    private
    function processInvoice($customer, $invoice) {
        $this->info('Processing Invoice '.$invoice->voucherNumber);
        $customer->invoices()->firstOrCreate([
            'lexoffice_id' => $invoice->id
        ]);
    }
}
