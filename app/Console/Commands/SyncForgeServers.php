<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\Forge\Endpoints\ServersEndpoint;
use Illuminate\Console\Command;

class SyncForgeServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forge:servers:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncronize Servers with Forge';

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
        $serversEndpoint = app()->make(ServersEndpoint::class);

        $response = $serversEndpoint->index();

        $this->withProgressBar(
            $response->servers,
            function ($server) {
                Server::updateOrCreate(
                    [
                        'forge_id' => $server->id
                    ]
                );
            }
        );
        return 0;
    }
}
