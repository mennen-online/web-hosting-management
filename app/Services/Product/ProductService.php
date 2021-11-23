<?php

namespace App\Services\Product;

use App\Jobs\Forge\CreateServer;
use App\Models\CustomerProduct;
use App\Models\Product;
use App\Services\Forge\Endpoints\ServersEndpoint;
use App\Services\Forge\Endpoints\SitesEndpoint;
use App\Services\Forge\Endpoints\WordPressEndpoint;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected Product $product,
        protected CustomerProduct $customerProduct,
        protected string $className = "",
        protected bool $exists = false

    ) {
        $this->className = 'App\\Services\\Product\\Models\\' . Str::ucfirst(Str::camel($this->product->name));
        $this->exists = class_exists($this->className);
    }

    public function productExists(): bool {
        return $this->exists;
    }

    public function getProductCallName(): string {
        return $this->className;
    }

    public function install(){
        if($this->exists) {
            $productClass = new $this->className(
                $this->customerProduct,
                app()->make(ServersEndpoint::class),
                app()->make(SitesEndpoint::class),
                app()->make(WordPressEndpoint::class)
            );
        }
    }
}