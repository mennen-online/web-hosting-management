<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SyncInternetworxDomainsPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internetworx:domains:price:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import new Domain Prices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $domainObject = app()->make(DomainObject::class);

        $response = $domainObject->indexPrice();

        $prices = collect($response['resData']['price'])->filter(function($price) {
            if(!Arr::has($price, 'tld-ace')) {
                return $price;
            }
        });

        $this->info("Process Domainprices");
        $this->withProgressBar($prices, function($price) {
            Product::updateOrCreate(
                [
                    'name' => $price['tld']
                ], [
                'description' => "Domain ".$price['tld'],
                'price'       => number_format($price['createPrice'], 2, '.', '')
            ]);
        });
        return 0;
    }
}
