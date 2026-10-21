<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueryBuilderPlayground extends Command
{
    protected $signature = 'app:query-builder-playground';

    protected $description = 'Thực hành Query Builder cho Coffee App';

    public function handle(): int
    {
        $this->info('1. SELECT: danh sách sản phẩm');

        $products = DB::table('products')
            ->select('id', 'name', 'price')
            ->orderBy('id')
            ->limit(5)
            ->get();

        $this->table(
            ['ID', 'Tên', 'Giá'],
            $products->map(fn ($product) => [
                $product->id,
                $product->name,
                $product->price,
            ])->all()
        );

        $this->info('2. WHERE + ORDER BY: giá >= 35000');

        $filtered = DB::table('products')
            ->where('price', '>=', 35000)
            ->orderByDesc('price')
            ->orderBy('id')
            ->get(['id', 'name', 'price']);

        $this->table(
            ['ID', 'Tên', 'Giá'],
            $filtered->map(fn ($product) => [
                $product->id,
                $product->name,
                $product->price,
            ])->all()
        );

        $this->info('3. LEFT JOIN: sản phẩm và danh mục');

        $joined = DB::table('products')
            ->leftJoin(
                'categories',
                'products.category_id',
                '=',
                'categories.id'
            )
            ->select(
                'products.id',
                'products.name',
                'categories.name as category_name'
            )
            ->orderBy('products.id')
            ->limit(10)
            ->get();

        $this->table(
            ['ID', 'Sản phẩm', 'Danh mục'],
            $joined->map(fn ($product) => [
                $product->id,
                $product->name,
                $product->category_name ?? 'Chưa phân loại',
            ])->all()
        );

        $this->info('4. So sánh Query Builder và Eloquent');

        $queryBuilderIds = DB::table('products')
            ->where('price', '>=', 35000)
            ->orderByDesc('price')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $eloquentIds = Product::query()
            ->where('price', '>=', 35000)
            ->orderByDesc('price')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if ($queryBuilderIds !== $eloquentIds) {
            $this->error('Kết quả không khớp.');

            return self::FAILURE;
        }

        $this->info('Hai cách truy vấn trả về cùng danh sách ID.');

        $this->info('5. INSERT + UPDATE trong transaction');

        // Luôn rollback để chạy lại bài thực hành mà không giữ dữ liệu demo.
        DB::beginTransaction();

        try {
            $now = now();

            $categoryId = DB::table('categories')->insertGetId([
                'name' => 'Query Builder Demo',
                'description' => 'Dữ liệu thử, sẽ rollback',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $productId = DB::table('products')->insertGetId([
                'name' => 'Cà phê Query Builder Demo',
                'description' => 'Sản phẩm thực hành',
                'price' => 40000,
                'currency' => 'VND',
                'display_image_url' => Product::DEFAULT_IMAGE,
                'category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->line("Đã thêm sản phẩm demo ID: {$productId}");

            DB::table('products')
                ->where('id', $productId)
                ->update([
                    'price' => 45000,
                    'updated_at' => now(),
                ]);

            $newPrice = DB::table('products')
                ->where('id', $productId)
                ->value('price');

            $this->line("Giá sau cập nhật: {$newPrice}");
        } finally {
            DB::rollBack();
        }

        $this->info('Đã rollback, không giữ dữ liệu demo.');

        return self::SUCCESS;
    }
}
