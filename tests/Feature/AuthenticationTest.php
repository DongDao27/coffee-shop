<?php

namespace Tests\Feature;

use App\Livewire\ApiTokenManager;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_views_render(): void
    {
        foreach (['/login', '/register', '/forgot-password'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/reset-password/example-token?email=lab@example.test')->assertOk();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_registration_verification_login_and_logout(): void
    {
        Notification::fake();
        $this->post('/register', ['name' => 'Lab', 'email' => 'lab@example.test',
            'password' => 'Password-123!', 'password_confirmation' => 'Password-123!',
        ])->assertRedirect('/dashboard');
        $user = User::query()->where('email', 'lab@example.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/dashboard')->assertRedirect('/email/verify');
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
        $this->get($url)->assertRedirect();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/dashboard')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password-123!'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_requires_valid_token_and_changes_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        $notification = Notification::sent($user, ResetPassword::class)->first();
        $this->assertNotNull($notification);
        $data = ['email' => $user->email, 'password' => 'New-Password-123!', 'password_confirmation' => 'New-Password-123!'];
        $this->post('/reset-password', [...$data, 'token' => 'invalid'])->assertSessionHasErrors('email');
        $this->post('/reset-password', [...$data, 'token' => $notification->token])->assertRedirect('/login');
        $this->assertTrue(Hash::check('New-Password-123!', $user->fresh()->password));
    }

    public function test_profile_password_updates_and_account_deletion_are_scoped_to_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->get('/profile')->assertOk();
        $this->put('/user/profile-information', ['name' => 'New Name', 'email' => $user->email, 'id' => $other->id])->assertSessionHasNoErrors();
        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertNotSame('New Name', $other->fresh()->name);
        $this->put('/user/password', ['current_password' => 'wrong', 'password' => 'Changed-123!', 'password_confirmation' => 'Changed-123!'])->assertSessionHasErrorsIn('updatePassword', 'current_password');
        $this->put('/user/password', ['current_password' => 'password', 'password' => 'Changed-123!', 'password_confirmation' => 'Changed-123!'])->assertSessionHasNoErrors();
        $user->createToken('deleted-account-token', ['read']);
        $this->delete('/profile', ['password' => 'Changed-123!'])->assertRedirect('/');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_two_factor_enable_confirm_login_challenge_recovery_and_disable(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);
        $this->postJson('/user/two-factor-authentication')->assertSuccessful();
        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $this->getJson('/user/two-factor-qr-code')->assertOk()->assertJsonStructure(['svg']);
        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->postJson('/user/confirmed-two-factor-authentication', ['code' => $code])->assertSuccessful();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $recovery = $user->fresh()->recoveryCodes()[0];
        $this->post('/logout');
        $this->app['auth']->forgetGuards();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/two-factor-challenge');
        $this->assertGuest();
        $this->post('/two-factor-challenge', ['code' => 'invalid'])->assertSessionHasErrors('code');
        $this->post('/two-factor-challenge', ['recovery_code' => $recovery])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($recovery, $user->fresh()->recoveryCodes());
        $this->withSession(['auth.password_confirmed_at' => time()])->deleteJson('/user/two-factor-authentication')->assertSuccessful();
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_api_requires_token_and_enforces_abilities_and_revocation(): void
    {
        $this->getJson('/api/v1/products')->assertUnauthorized();
        $user = User::factory()->create();
        $token = $user->createToken('read-only', ['read']);
        $this->withToken($token->plainTextToken)->getJson('/api/v1/products')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($token->plainTextToken)->postJson('/api/v1/products', [])->assertForbidden();
        $token->accessToken->delete();
        $this->app['auth']->forgetGuards();
        $this->withToken($token->plainTextToken)->getJson('/api/v1/products')->assertUnauthorized();
    }

    public function test_two_factor_login_accepts_a_valid_authenticator_code(): void
    {
        $secret = (new Google2FA)->generateSecretKey();
        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-example'])),
            'two_factor_confirmed_at' => now(),
        ])->save();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/two-factor-challenge');
        $this->assertGuest();
        $this->post('/two-factor-challenge', ['code' => (new Google2FA)->getCurrentOtp($secret)])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_repeated_invalid_logins_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'throttle@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'throttle@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_token_manager_cannot_manage_another_users_token(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('Other user token', ['read']);
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($user)->test(ApiTokenManager::class)
            ->call('manageApiTokenPermissions', $token->accessToken->id);
    }

    public function test_token_manager_creates_updates_and_revokes_owned_tokens(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $otherToken = $other->createToken('other', ['read']);
        $this->actingAs($user);
        $component = Livewire::test(ApiTokenManager::class)
            ->set('createApiTokenForm.name', 'Lab token')
            ->set('createApiTokenForm.permissions', ['read', 'unknown'])
            ->call('createApiToken')->assertHasNoErrors();
        $token = $user->tokens()->firstOrFail();
        $this->assertSame(['read'], $token->abilities);
        $this->assertStringContainsString('|', $component->get('plainTextToken'));
        $component->call('manageApiTokenPermissions', $token->id)
            ->set('updateApiTokenForm.permissions', ['read', 'create'])
            ->call('updateApiToken');
        $this->assertSame(['read', 'create'], $token->fresh()->abilities);
        $component->call('confirmApiTokenDeletion', $token->id)->call('deleteApiToken');
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->id]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $otherToken->accessToken->id]);
    }
}
