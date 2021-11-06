<?php

namespace App\Jobs\Forge;

use App\Models\CustomerProduct;
use App\Services\Forge\Endpoints\WordPressEndpoint;
use App\Services\Product\Models\WordPress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWordPressInstance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerProduct $customerProduct,
        protected int $siteId
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
        app()->make(WordPressEndpoint::class)->install($this->customerProduct->server, $this->siteId);
    }
}
