<?php

namespace App\Observers;

use App\Models\Domain;
use App\Services\Internetworx\Objects\DomainObject;

class DomainObserver
{
    public function created(Domain $domain)
    {
    }

    public function updated(Domain $domain)
    {
        if ($domain->registrar_id === null) {
            app()->make(DomainObject::class)->create($domain);
        }
    }

    public function deleted(Domain $domain)
    {
        app()->make(DomainObject::class)->runOut($domain);
    }

    public function restored(Domain $domain)
    {
        app()->make(DomainObject::class)->renew($domain);
    }

    public function forceDeleted(Domain $domain)
    {
        app()->make(DomainObject::class)->delete($domain);
    }
}
