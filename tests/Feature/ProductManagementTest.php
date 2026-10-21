<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_manage_products_from_web_forms(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::factory()->create();
        $data = ['name' => 'Cà phê web', 'description' => 'Web demo', 'price' => 40000, 'currency' => 'VND', 'category_id' => $category->id];
        $this->get('/manage/products')->assertOk()->assertSee('Thêm sản phẩm');
        $this->get('/manage/products/create')->assertOk();
        $this->post('/manage/products', $data)->assertRedirect('/manage/products');
        $product = Product::where('name', $data['name'])->firstOrFail();
        $this->get('/manage/products/'.$product->id.'/edit')->assertOk()->assertSee($data['name']);
        $this->put('/manage/products/'.$product->id, [...$data, 'price' => 45000])->assertRedirect('/manage/products');
        $this->assertSame('45000.00', $product->fresh()->price);
        $this->put('/manage/products/'.$product->id, [...$data, 'price' => -1000])->assertSessionHasErrors('price');
        $this->delete('/manage/products/'.$product->id)->assertRedirect('/manage/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_guests_and_unverified_users_cannot_modify_products(): void
    {
        $product = Product::factory()->create();
        $this->get('/manage/products')->assertRedirect('/login');
        $this->actingAs(User::factory()->unverified()->create());
        $this->post('/manage/products', [])->assertRedirect('/email/verify');
        $this->delete('/manage/products/'.$product->id)->assertRedirect('/email/verify');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_products_used_in_carts_are_not_deleted(): void
    {
        $item = CartItem::factory()->create();
        $this->actingAs(User::factory()->create());
        $this->from('/manage/products')->delete('/manage/products/'.$item->product_id)->assertSessionHasErrors('product');
        $this->assertDatabaseHas('products', ['id' => $item->product_id]);
    }

    public function test_verification_page_has_resend_and_can_send_notification(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee('Gửi lại email xác minh');
        $this->post('/email/verification-notification')->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_local_mail_log_link_verifies_current_user_and_unlocks_token_screen(): void
    {
        $this->app['env'] = 'local';
        config(['mail.default' => 'log']);
        $user = User::factory()->unverified()->create();
        $response = $this->actingAs($user)->get('/email/verify')->assertOk()->assertSee('Mở liên kết xác minh (local)');
        $url = $response->viewData('localVerificationUrl');
        $this->assertNotNull($url);
        $this->get($url)->assertRedirect();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->withSession(['auth.password_confirmed_at' => time()])->get('/api-tokens')->assertOk();
    }

    public function test_production_never_exposes_local_verification_link(): void
    {
        $this->app['env'] = 'production';
        config(['mail.default' => 'log']);
        $this->actingAs(User::factory()->unverified()->create())->get('/email/verify')
            ->assertOk()->assertDontSee('Mở liên kết xác minh (local)')->assertViewHas('localVerificationUrl', null);
    }
}
