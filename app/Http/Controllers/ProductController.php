<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductListingResource;
use App\Http\Resources\ProductResource;
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

    public function show(Product $product) {

        return Inertia::render('Product/Show', [
            
            'product' => new ProductResource($product),
            'variationOptions' => request('options', []),
        ]);
    }
}
