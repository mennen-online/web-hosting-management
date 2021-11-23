<?php

namespace App\Services\Product\Models;

use App\Jobs\Forge\CreateServer;
use App\Jobs\Forge\CreateSite;
use App\Jobs\Forge\CreateWordPressInstance;
use App\Jobs\Internetworx\UpdateDns;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use App\Services\Forge\Endpoints\SitesEndpoint;
use App\Services\Forge\Endpoints\WordPressEndpoint;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class WordPress
{
    public function __construct(
        protected CustomerProduct $customerProduct,
        protected ServersEndpoint $serversEndpoint,
        protected SitesEndpoint $sitesEndpoint,
        protected WordPressEndpoint $wordPressEndpoint
    ) {
        if($this->customerProduct->server === null) {
            CreateServer::dispatch($this->customerProduct);
        }
        UpdateDns::dispatch($this->customerProduct)->delay(now()->addMinutes(10));
        CreateSite::dispatch($this->customerProduct)->delay(now()->addMinutes(20));
    }
}