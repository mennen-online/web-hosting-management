<?php

namespace App\Jobs\Forge;

use App\Models\CustomerProduct;
use App\Services\Forge\Endpoints\SitesEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CreateSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerProduct $customerProduct
    )
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
        $site = app()->make(SitesEndpoint::class)->create($this->customerProduct->server, $this->customerProduct->domain, [
            'project_type' => 'php',
            'directory' => '/'.Str::snake($this->customerProduct->domain->name),
            'username' => Str::slug($this->customerProduct->domain->name),
            'database' => Str::slug($this->customerProduct->domain->name),
            'php_version' => 'php74'
        ]);

        CreateWordPressInstance::dispatch($this->customerProduct->server, $site->site->id);
    }
}
