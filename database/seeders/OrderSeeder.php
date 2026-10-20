<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->where('code', 'OR-DEMO-001')->exists()) {
            return;
        }
        $cart = Cart::factory()->create();
        foreach (Product::query()->take(3)->get() as $product) {
            CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1]);
        }
        Order::factory()->create(['cart_id' => $cart->id, 'code' => 'OR-DEMO-001']);
    }
}
