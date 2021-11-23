<?php

namespace Tests\Feature\Product;

use App\Jobs\Forge\CreateServer;
use App\Jobs\Forge\CreateSite;
use App\Models\CustomerProduct;
use App\Models\Product;
use App\Services\Product\Models\WordPress;
use App\Services\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use OhSeeSoftware\LaravelQueueFake\QueueFake;
use Tests\TestCase;

class WordPressTest extends TestCase
{
    protected Product $product;

    protected function setUp(): void {
        parent::setUp();

        $this->product = Product::factory()->create([
            'name' => 'WordPress',
            'description' => 'WordPress Simple Test',
            'price' => 120
        ]);
    }

    public function testWordPressInstallerClassExists() {
        $this->assertTrue(
            class_exists('App\\Services\\Product\\Models\\'. Str::ucfirst(Str::camel($this->product->name))),
            Str::kebab($this->product->name) . ' Class does not Exists'
        );
    }

    public function testProductInstallerQueuesJobs() {
        $customerProduct = null;

        QueueFake::wrap(function() use(&$customerProduct) {
            $customerProduct = CustomerProduct::factory()->for($this->product)->create();
        });

        $productService = new ProductService($this->product, customerProduct: $customerProduct);

        $this->assertTrue($productService->productExists());

        $this->assertSame(WordPress::class, $productService->getProductCallName());

        CreateServer::dispatch($customerProduct);
        CreateSite::dispatch($customerProduct)->delay(now()->addMinutes(20));
    }
}
