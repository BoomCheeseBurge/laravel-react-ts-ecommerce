<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
        ])->assignRole(RolesEnum::Admin->value);

        $user = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@gmail.com',
        ]);

        /**
         * Assign role and a vendor record for the corresponding user
         */
        $user->assignRole(RolesEnum::Vendor->value);
        Vendor::factory()->create([
            'user_id' => $user->id,
            'status' => VendorStatusEnum::Approved,
            'store_name' => fake()->word() . ' Store',
            'store_address' => fake()->address(),
        ]);

        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'doe@gmail.com',
        ])->assignRole(RolesEnum::User->value);
    }
}
