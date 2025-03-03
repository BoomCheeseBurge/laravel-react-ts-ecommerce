<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\ProductListingResource;
use App\Http\Resources\ProductResource;
use App\Models\Department;
use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function home(Request $request): InertiaResponse {

        // Get the keyword from the searchbar
        $keyword = $request->query('keyword');
        
        $products = Product::forWebsite()
                            ->when($keyword, function ($query, $keyword) {
                                $query->where(function ($query) use ($keyword) {
                                    $query->where('title', 'LIKE', "%{$keyword}%")
                                        ->orWhere("description", "LIKE", "%{$keyword}%");
                                });
                            })
                            ->paginate(12);

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

    public function byDepartment(Request $request, Department $department): InertiaResponse
    {
        // Abort if department is INACTIVE
        abort_unless($department->active, 404);

        // Get the keyword from the searchbar
        $keyword = $request->query('keyword');

        // Get the products based on the department and keyword
        $products = Product::forWebsite()
                            ->where('department_id', $department->id)
                            ->when($keyword, function ($query, $keyword) {
                                $query->where(function ($query) use ($keyword) {
                                    $query->where('title', 'LIKE', "%{$keyword}%")
                                        ->orWhere("description", "LIKE", "%{$keyword}%");
                                });
                            })
                            ->paginate();
        
        return Inertia::render('Department/Index', [
            'department' => new DepartmentResource($department),
            'products' => ProductListingResource::collection($products),
        ]);
    }
}
