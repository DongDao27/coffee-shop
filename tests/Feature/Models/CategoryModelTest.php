<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_able_to_get_products()
    {
        $category = Category::factory()
            ->has(Product::factory()->count(3), 'products')
            ->create();

        $this->assertInstanceOf(Product::class, $category->products->random());

        $this->assertTrue(count($category->products) === 3);
    }
}
