<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cafe',
                'description' => 'Các loại cà phê và thức uống từ cà phê.',
            ],
            [
                'name' => 'Chicken',
                'description' => 'Các món ăn được chế biến từ gà.',
            ],
        ];

        foreach ($categories as $category) {
            if (! Category::query()->where('name', $category['name'])->exists()) {
                Category::factory()->create($category);
            }
        }
    }
}
