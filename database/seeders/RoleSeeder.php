<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Spatie roles for the users
        $adminRole = Role::create(["name"=> RolesEnum::Admin->value]);
        $vendorRole = Role::create(["name"=> RolesEnum::Vendor->value]);
        $userRole = Role::create(["name"=> RolesEnum::User->value]);

        // Define Spatie permissions for the users
        $approveVendors = Permission::create(["name"=> PermissionsEnum::ApproveVendors->value]);
        $sellProducts = Permission::create(["name"=> PermissionsEnum::SellProducts->value]);
        $buyProducts = Permission::create(["name"=> PermissionsEnum::BuyProducts->value]);

        // Sync permissions to the roles
        $adminRole->syncPermissions([
            $buyProducts,
            $sellProducts,
            $approveVendors
        ]);
        $vendorRole->syncPermissions([
            $buyProducts,
            $sellProducts,
        ]);
        $userRole->syncPermissions([
            $buyProducts,
        ]);
    }
}
