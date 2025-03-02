<?php

namespace App\Http\Controllers;

use Devscast\Pexels\Client;
use Devscast\Pexels\Parameter\SearchParameters;
use Inertia\Inertia;
use App\Models\Vendor;
use App\Models\Product;
use App\Enums\RolesEnum;
use Illuminate\Http\Request;
use App\Enums\VendorStatusEnum;
use Illuminate\Validation\Rule;
use App\Http\Resources\ProductListingResource;
use Inertia\Response as InertiaResponse;

class VendorController extends Controller
{
    public function profile(Vendor $vendor): InertiaResponse
    {
        $products = Product::forWebsite()
                            ->belongsToVendor($vendor->user_id)
                            ->paginate();

        return Inertia::render('Vendor/Profile', [
            'vendor' => $vendor,
            'products' => ProductListingResource::collection($products),
        ]);
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
