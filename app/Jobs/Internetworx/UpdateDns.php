<?php

namespace App\Jobs\Internetworx;

use App\Jobs\Forge\CreateServer;
use App\Models\Domain;
use App\Models\Server;
use App\Services\Internetworx\Objects\DomainObject;
use App\Services\Internetworx\Objects\NameserverObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateDns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Domain $domain, protected Server $server)
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
        $this->domain->refresh();

        $this->server->refresh();


        Log::info('Update Domain Object => Creating Nameserver');
        $domain = app()->make(DomainObject::class);
        $nameserver = app()->make(NameserverObject::class);

        $domain->setDefaultNameserver($this->domain);
        $nameserver->create($this->domain, $this->server);
    }
}
