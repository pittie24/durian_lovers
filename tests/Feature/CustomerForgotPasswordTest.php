<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Notifications\CustomerResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class CustomerForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_a_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.notice'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('password_reset_email', 'cu******@example.com');

        Notification::assertSentTo($user, CustomerResetPasswordNotification::class);
    }

    public function test_admin_email_is_rejected_from_customer_reset_flow(): void
    {
        Notification::fake();

        Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->from(route('password.request'))->post(route('password.email'), [
            'email' => 'admin@example.com',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_customer_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_forgot_password_request_is_rate_limited(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), [
                'email' => 'customer@example.com',
            ]);
        }

        $response = $this->post(route('password.email'), [
            'email' => 'customer@example.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_check_email_page_can_display_masked_email(): void
    {
        $response = $this->withSession([
            'password_reset_email' => 'cu******@example.com',
        ])->get(route('password.notice'));

        $response->assertOk();
        $response->assertSee('cu******@example.com');
    }
}
