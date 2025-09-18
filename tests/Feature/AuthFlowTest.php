<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_sends_verification_email_and_blocks_dashboard_until_verified(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Admin',
            'email' => 'user@example.com',
            'password' => 'P@ssw0rd!',
            'password_confirmation' => 'P@ssw0rd!',
        ]);

        // App may redirect to HOME (/dashboard) or verification page.
        // Just assert that a redirect happened.
        $response->assertRedirect();

        $user = User::whereEmail('user@example.com')->first();
        $this->assertNotNull($user);

        // Unverified right after registration
        $this->assertFalse($user->hasVerifiedEmail());

        // Verification email is sent
        Notification::assertSentTo($user, VerifyEmail::class);

        // Unverified users should be blocked from dashboard
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/verify-email');
    }

    public function test_verifies_email_and_allows_dashboard(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($url);

        // It should redirect somewhere (usually /dashboard?verified=1)
        $response->assertRedirect();

        // Be flexible about the query string (?verified=1 or none)
        $location = $response->headers->get('Location');
        $this->assertTrue(
            str_starts_with($location, url('/dashboard')),
            "Expected redirect to start with ".url('/dashboard')." but got: {$location}"
        );

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    public function test_sends_password_reset_link_and_resets_password(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->get('/reset-password/'.$notification->token.'?email='.urlencode($user->email));
            $response->assertOk();

            $post = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'NewP@ssw0rd!',
                'password_confirmation' => 'NewP@ssw0rd!',
            ]);

            $post->assertRedirect('/login');
            return true;
        });
    }
}
