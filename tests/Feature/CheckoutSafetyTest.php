<?php

namespace Tests\Feature;

use App\Livewire\Checkout\AddCartItemPopup;
use App\Livewire\SettingDropdown;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cart_cannot_create_order(): void
    {
        $this->post('/checkout', [
            'full_name' => 'Lab', 'phone_number' => '0901234567',
            'email' => 'lab@example.test', 'shipping_address' => 'Lab address',
        ])->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_unsigned_order_pages_are_forbidden(): void
    {
        $order = Order::factory()->create();
        $this->get('/checkout/'.$order->id)->assertForbidden();
        $this->get('/order-complete/'.$order->id)->assertForbidden();
        $this->get($order->getCheckoutConfirmtionPath())->assertOk();
    }

    public function test_readding_product_replaces_quantity_and_does_not_duplicate_items(): void
    {
        $service = new CartService;
        $product = Product::factory()->create(['price' => 35000]);
        $service->addCartItem(['product_id' => $product->id, 'quantity' => 2]);
        $service->addCartItem(['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertEquals(105000, $service->getCart()->total_amount);
        $service->removeCartItem($product->id);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_rejects_zero_quantity(): void
    {
        $product = Product::factory()->create();
        $this->expectException(ValidationException::class);
        (new CartService)->addCartItem(['product_id' => $product->id, 'quantity' => 0]);
    }

    public function test_popup_validates_quantity_before_saving(): void
    {
        $product = Product::factory()->create();
        Livewire::test(AddCartItemPopup::class)->call('displayAddCartItem', $product->id)
            ->set('quantity', 0)->call('addCartItem')->assertHasErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_settings_persist_and_locale_applies_to_next_request(): void
    {
        Livewire::test(SettingDropdown::class)->call('setTheme', 'theme-dark')
            ->assertDispatched('theme-changed')->call('setLocale', 'vi');
        $this->assertSame('theme-dark', session('theme'));
        $this->assertSame('vi', session('locale'));
        $this->get('/')->assertOk()->assertSee('lang="vi"', false)->assertSee('theme-dark');
    }
}
