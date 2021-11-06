<?php

namespace App\Observers;

use App\Jobs\Internetworx\CreateDomain;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Product;
use App\Services\Internetworx\Objects\DomainObject;
use Illuminate\Support\Facades\Session;

class DomainObserver
{
    public function created(Domain $domain) {
        CreateDomain::dispatch($domain);

        $data = Session::get($domain->name . '_customer-product');

        CustomerProduct::find($data['customer_product_id'])->update(['domain_id' => $domain->id]);
    }
}
