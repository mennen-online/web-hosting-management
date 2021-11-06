<?php

namespace App\Jobs\Internetworx;

use App\Models\Domain;
use App\Models\User;
use App\Notifications\Customer\DomainRegistrationSuccessful;
use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected Domain $domain
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
        app()->make(DomainObject::class)->create($this->domain);

        $this->domain->refresh();

        if($this->domain->registrar_id !== null) {
            $this->domain->customerProduct->customer->user->notify(new DomainRegistrationSuccessful($this->domain));
        }
    }
}
