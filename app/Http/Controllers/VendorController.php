<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function profile(Vendor $vendor): void
    {
        # code...
    }

    /**
     * Upsert vendor user
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function store(Request $request): void
    {
        // Get the current auth user info
        $user = $request->user();

        $request->validate([
            'store_name' => [
                                'required', 
                                'regex:/^[a-z0-9-]+$/', 
                                Rule::unique('vendors', 'store_name')->ignore($user->id, 'user_id'),
                            ],
            'store_address' => 'nullable',
        ], [
            'store_name.regex' => 'Store name MUST ONLY contain lowercase alphanumeric characters and dashes.'
        ]);

        // Check if this user is a vendor, else create into one
        $vendor = $user->vendor ?: new Vendor();

        // Assign the updated (or existing) values
        $vendor->user_id = $user->id;
        $vendor->status = VendorStatusEnum::Approved->value; // Auto-approve user as vendor
        $vendor->store_name = $request->store_name;
        $vendor->store_address = $request->store_address;
        $vendor->save();

        // Assign user to vendor role
        $user->assignRole(RolesEnum::Vendor);

        return;
    }
}
