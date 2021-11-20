<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
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
}
