<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Cà phê sữa đá',
                'description' => 'Cà phê đậm vị pha cùng sữa đặc và đá.',
                'price' => 35_000,
                'currency' => 'VND',
                'display_image_url' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735',
                'category' => 'Cafe',
            ],
            [
                'name' => 'Cà phê đen đá',
                'description' => 'Cà phê rang xay nguyên chất phục vụ cùng đá.',
                'price' => 30_000,
                'currency' => 'VND',
                'display_image_url' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085',
                'category' => 'Cafe',
            ],
            [
                'name' => 'Gà rán giòn',
                'description' => 'Gà rán lớp vỏ giòn và phần thịt mềm.',
                'price' => 65_000,
                'currency' => 'VND',
                'display_image_url' => 'https://images.unsplash.com/photo-1562967914-608f82629710',
                'category' => 'Chicken',
            ],
        ];

        foreach ($products as $product) {
            $categoryName = $product['category'];
            unset($product['category']);

            $category = Category::query()->where('name', $categoryName)->firstOrFail();

            if (! Product::query()->where('name', $product['name'])->exists()) {
                Product::factory()->for($category)->create($product);
            }
        }
    }
}
