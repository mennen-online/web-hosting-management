<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected Product $product,
        protected string $className = "",
        protected bool $exists = false
    ) {
        $this->className = 'App\\Services\\Product\\' . Str::ucfirst(Str::camel($this->product->name));
        $this->exists = class_exists($this->className);
    }

    public function install(){
        if($this->exists) {
            $productClass = new $this->className();
        }
    }
}