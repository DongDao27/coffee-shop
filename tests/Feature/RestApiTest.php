<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\PaginationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_crud_and_rejected_updates_preserve_data(): void
    {
        $category = Category::factory()->create();
        $payload = [
            'name' => 'Cà phê thử nghiệm',
            'description' => 'Sản phẩm dùng kiểm tra API',
            'price' => 40000,
            'currency' => 'VND',
            'display_image_url' => 'https://example.com/coffee.jpg',
            'category_id' => $category->id,
        ];

        $created = $this->postJson('/api/v1/products', $payload)
            ->assertCreated()->assertJsonPath('category.id', $category->id);
        $url = '/api/v1/products/'.$created->json('id');
        $this->getJson($url)->assertOk()->assertJsonPath('name', $payload['name']);
        $this->patchJson($url, ['price' => 45000])->assertOk()
            ->assertJsonPath('price', '45000.00')->assertJsonPath('name', $payload['name']);
        $this->patchJson($url, ['price' => -1000])->assertUnprocessable()->assertJsonValidationErrors('price');
        $this->postJson('/api/v1/products', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'description', 'price', 'currency', 'display_image_url']);
        $this->patchJson($url, ['category_id' => 999999])->assertUnprocessable()->assertJsonValidationErrors('category_id');
        $this->getJson($url)->assertOk()->assertJsonPath('price', '45000.00');
        $this->deleteJson($url)->assertNoContent();
        $this->getJson($url)->assertNotFound();
    }

    public function test_product_search_filter_sort_and_query_validation(): void
    {
        $cafe = Category::factory()->create();
        $cheap = Product::factory()->for($cafe)->create(['name' => 'Cà phê đen', 'price' => 30000]);
        $expensive = Product::factory()->for($cafe)->create(['name' => 'Cà phê sữa', 'price' => 35000]);
        $chicken = Product::factory()->create(['name' => 'Gà rán', 'price' => 65000]);

        $this->getJson('/api/v1/products?sort=price_asc')->assertOk()
            ->assertJsonPath('data.0.id', $cheap->id)->assertJsonPath('data.2.id', $chicken->id);
        $this->getJson('/api/v1/products?sort=price_desc')->assertOk()
            ->assertJsonPath('data.0.id', $chicken->id)->assertJsonPath('data.2.id', $cheap->id);
        $response = $this->getJson('/api/v1/products?'.http_build_query([
            'search' => 'phê', 'category_id' => $cafe->id, 'sort' => 'price_desc',
        ]))->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', $expensive->id);
        $this->assertSame([$cafe->id], array_values(array_unique(array_column($response->json('data'), 'category_id'))));
        $this->getJson('/api/v1/products?sort=invalid')->assertUnprocessable()->assertJsonValidationErrors('sort');
    }

    public function test_category_crud_validation_and_missing_records(): void
    {
        $created = $this->postJson('/api/v1/categories', ['name' => 'Tea', 'description' => 'Trà'])
            ->assertCreated();
        $url = '/api/v1/categories/'.$created->json('id');
        $this->getJson($url)->assertOk()->assertJsonPath('name', 'Tea');
        $this->postJson('/api/v1/categories', ['name' => 'Tea'])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/v1/categories', [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->patchJson($url, ['name' => 'Tea'])->assertOk();
        $this->patchJson($url, ['description' => 'Updated'])->assertOk()->assertJsonPath('description', 'Updated');
        $product = Product::factory()->create(['category_id' => $created->json('id')]);
        $this->deleteJson($url)->assertNoContent();
        $this->assertNull($product->fresh()->category_id);
        $this->getJson($url)->assertNotFound();
    }

    public function test_one_thousand_demo_categories_have_distinct_pages_and_seed_is_repeatable(): void
    {
        $this->seed(PaginationDemoSeeder::class);
        $this->seed(PaginationDemoSeeder::class);
        $this->assertDatabaseCount('categories', 1000);
        $page1 = $this->getJson('/api/v1/categories?page=1')->assertOk()
            ->assertJsonPath('total', 1000)->assertJsonPath('last_page', 100)->assertJsonCount(10, 'data');
        $page2 = $this->getJson('/api/v1/categories?page=2')->assertOk()->assertJsonCount(10, 'data');
        $this->assertSame([], array_values(array_intersect(
            array_column($page1->json('data'), 'id'), array_column($page2->json('data'), 'id'),
        )));
    }
}
