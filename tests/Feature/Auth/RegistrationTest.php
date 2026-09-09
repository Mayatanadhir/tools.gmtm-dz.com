<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
<<<<<<< HEAD
use Illuminate\Auth\Notifications\VerifyEmail;
=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

<<<<<<< HEAD
    public function test_email_verification_notification_is_sent_upon_registration(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Verify User',
            'email' => 'verify@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'verify@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

=======
>>>>>>> 1355bd68bffa8592fe252627c65c6998eba406ce
    public function test_newly_registered_user_is_automatically_assigned_default_role(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->post('/register', [
            'name' => 'Default Role User',
            'email' => 'defaultrole@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'defaultrole@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('User'));
    }
}
