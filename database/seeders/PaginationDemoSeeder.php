<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class PaginationDemoSeeder extends Seeder
{
    public function run(): void
    {
        for ($number = 1; $number <= 1000; $number++) {
            $name = sprintf('Pagination demo %04d', $number);

            if (! Category::query()->where('name', $name)->exists()) {
                Category::factory()->create(['name' => $name]);
            }
        }
    }
}
