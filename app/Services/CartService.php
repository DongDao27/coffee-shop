<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Validator;

class CartService
{
    /**
     * Add new or update exists cart item
     *
     * @param  mixed  $item
     */
    public function addCartItem($item): bool
    {
        $item = Validator::make($item, [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();
        $cart = $this->getCart();

        $query_condition = [
            'cart_id' => $cart->id, 'product_id' => $item['product_id'],
        ];

        CartItem::updateOrCreate($query_condition, $item);

        return true;
    }

    /**
     * Remove exists cart item
     *
     * @param  mixed  $product_id
     */
    public function removeCartItem($product_id): bool
    {
        $cart_item = $this->getCartItem($product_id);

        if ($cart_item) {
            $cart_item->forceDelete();

            return true;
        }

        return false;
    }

    public function getCartItem($product_id): ?CartItem
    {
        $cart = $this->getCart();

        return $cart->items()->where('product_id', $product_id)->first();
    }

    /**
     * Get cart from session
     *
     * @return mixed
     */
    public function getCart(): Cart
    {
        $user_id = UserService::getUserIdFromSession();

        $cart = Cart::query()->with('items.product')
            ->where('user_id', $user_id)
            ->doesntHave('order')
            ->first();

        if (! $cart) {
            $cart = Cart::query()->create([
                'user_id' => $user_id,
            ]);
        }

        return $cart;
    }
}
