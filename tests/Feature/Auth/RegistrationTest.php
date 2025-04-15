<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Enums\RolesEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        // Create role for user
        Role::create(["name"=> RolesEnum::User->value]);
        
        $userData = [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'password' => 'P@ssw0rd123',
            'password_confirmation' => 'P@ssw0rd123',
        ];
    
        $response = $this->post('/register', $userData);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }
}
