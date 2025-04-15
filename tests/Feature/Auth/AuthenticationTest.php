<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $vendorUser;
    private User $adminUser;

    protected function setup(): void
    {
        parent::setUp();

        // Define Spatie roles for the users
        Role::create(["name"=> RolesEnum::Admin->value]);
        Role::create(["name"=> RolesEnum::Vendor->value]);
        Role::create(["name"=> RolesEnum::User->value]);

        // Create a regular user
        $this->user = User::factory()->create();

        // Create a vendor user
        $this->vendorUser = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@gmail.com',
        ])->assignRole(RolesEnum::Vendor->value);

        Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
        ]);

        // Create a admin user
        $this->adminUser = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ])->assignRole(RolesEnum::Admin->value);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->actingAs($this->user)->post('/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_vendors_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => $this->vendorUser->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => $this->adminUser->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(route('filament.admin.pages.dashboard', absolute: false));
    }
    
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $this->assertGuest();

        $response->assertRedirect('/');
    }

    public function test_users_can_not_access_filament_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('filament.admin.pages.dashboard'));

        $response->assertStatus(403);
    }
}
