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
    public function profile(Request $request, Vendor $vendor): InertiaResponse
    {
        // Get the keyword from the searchbar
        $keyword = $request->query('keyword');
        
        $products = Product::forWebsite()
                            ->when($keyword, function ($query, $keyword) {
                                $query->where(function ($query) use ($keyword) {
                                    $query->where('title', 'LIKE', "%{$keyword}%")
                                        ->orWhere("description", "LIKE", "%{$keyword}%");
                                });
                            })
                            ->belongsToVendor($vendor->user_id)
                            ->paginate();

        $store_image = $this->generatePexelImages()[0];

        return Inertia::render('Vendor/Profile', [
            'vendor' => $vendor,
            'products' => ProductListingResource::collection($products),
            'storeImg' => $store_image,
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

    public function generatePexelImages(): array
    {
        // Set up the Pexel client
        $pexels = new Client(config('app.pexels_key'));

        // Fetch the photos from Pexels API
        $result = $pexels->searchPhotos('Products', new SearchParameters(orientation: 'landscape', size: 'large'));

        return $result->photos;
    }
}
