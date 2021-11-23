<?php

namespace App\Jobs\Forge;

use App\Jobs\Internetworx\UpdateDns;
use App\Models\CustomerProduct;
use App\Models\Server;
use App\Services\Forge\Endpoints\CredentialsEndpoint;
use App\Services\Forge\Endpoints\RegionsEndpoint;
use App\Services\Forge\Endpoints\ServersEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateServer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected CustomerProduct $customerProduct)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $credentials = app()->make(CredentialsEndpoint::class)->index()->credentials;
        $regions = app()->make(RegionsEndpoint::class)->index();
        $hetznerRegions = collect($regions->regions->hetzner);
        $region = collect($hetznerRegions)->first();
        $sizes = collect($region->sizes);
        $size = $sizes->first();
        Log::info('Request Server Creation at Hetzner Cloud');
        $serverObject = app()->make(ServersEndpoint::class)->create([
            'provider' => 'hetzner',
            'name' => $this->customerProduct->domain->name,
            'region' => $region->id,
            'size' => $size->id,
            'type' => 'app',
            'credential_id' => collect($credentials)->first()->id,
            'php_version' => 'php74',
            'database_type' => 'mysql8'
        ]);
        Log::info('Server Created');
        Log::info(json_encode($serverObject));

        $server = Server::create([
            'forge_id' => $serverObject->server->id
        ]);

        $this->customerProduct->update(['server_id' => $server->id]);
    }
}
