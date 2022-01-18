<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\Lexoffice\Endpoints\InvoicesEndpoint;
use App\Services\Lexoffice\Endpoints\VoucherlistEndpoint;
use App\Services\Lexoffice\Lexoffice;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
            Customer::orderBy('number')->chunk(50, function ($customers) {
                $this->withProgressBar($customers, fn($customer) => $this->processImportCustomer($customer));
                sleep(60);
            });
        }
        return 0;
    }

    private function processImportCustomer($customer)
    {
        Lexoffice::getNewInvoiceNumbersByCustomer($customer);
    }
}
