<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Server;
use App\Observers\CustomerContactObserver;
use App\Observers\CustomerInvoiceObserver;
use App\Observers\CustomerObserver;
use App\Observers\CustomerProductObserver;
use App\Observers\DomainObserver;
use App\Observers\ServerObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Domain::observe(DomainObserver::class);
        Server::observe(ServerObserver::class);
        Customer::observe(CustomerObserver::class);
        CustomerContact::observe(CustomerContactObserver::class);
        CustomerProduct::observe(CustomerProductObserver::class);
        CustomerInvoice::observe(CustomerInvoiceObserver::class);
    }
}
