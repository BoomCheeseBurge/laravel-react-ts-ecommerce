<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListingResource;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function home() {

        $products = Product::published()->paginate(12);

        return Inertia::render("Home", [

            "products"=> ProductListingResource::collection($products),
        ]);
    }

    public function show(Product $product) {}
}
