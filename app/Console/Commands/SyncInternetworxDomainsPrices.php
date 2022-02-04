<?php

namespace App\Console\Commands;

use App\Models\DomainProduct;
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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domainObject = app()->make(DomainObject::class);

        $response = $domainObject->indexPrice();

        $this->line('Get Info for TLDs');

        $prices = $response->filter(
            function ($price) {
                if (!Arr::has($price, 'tld-ace') && !empty($price['tld'])) {
                    return $price;
                }
            }
        );

        $this->withProgressBar($prices, function ($price) {
            $domainBase = Arr::only($price, [
                'tld',
                'currency'
            ]);

            $data = array_merge(
                $domainBase,
                [
                    'period' => '1Y',
                    'reg_price' => Arr::get($price, 'createPrice', 0.00),
                    'renewal_price' => Arr::get($price, 'renewalPrice', 0.00),
                    'update_price' => Arr::get($price, 'updatePrice', 0.00),
                    'trade_price' => Arr::get($price, 'tradePrice', 0.00),
                    'transfer_price' => Arr::get($price, 'transferPrice', 0.00),
                    'whois_protection_price' => Arr::get($price, 'whoisProtectionPrice', 0.00),
                    'restore_price' => Arr::get($price, 'restorePrice', 0.00)
                ]
            );

            DomainProduct::updateOrCreate(
                $data
            );
        });
        return 0;
    }
}
