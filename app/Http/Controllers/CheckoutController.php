<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(CartService $cart)
    {
        return view('checkout.index', [
            'cart' => $cart->getCart(),
        ]);
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:30',
            'email' => 'required|email|max:255',
            'shipping_address' => 'required|string|max:1000',
            'coupon_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cartId = $cart->getCart()->id;
        $order = DB::transaction(function () use ($cartId, $validated) {
            $current = Cart::query()->lockForUpdate()->findOrFail($cartId);
            if ($current->order()->exists() || ! $current->items()->exists()) {
                throw ValidationException::withMessages(['cart' => __('Your cart is empty.')]);
            }

            return $current->order()->create([
                ...$validated, 'code' => 'OR-'.Str::upper(Str::random(12)),
            ]);
        });

        OrderCreated::dispatch($order);

        return redirect(URL::signedRoute('orders.complete', ['order' => $order->id]));
    }

    public function show(Request $request, Order $order)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        return view('checkout.show', compact('order'));
    }
}
