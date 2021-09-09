<?php

namespace App\Providers;

use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use INWX\Domrobot;

class InternetworxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
