<?php

namespace App\Http\Middleware;

use App\Enums\RolesEnum;
use App\Http\Resources\AuthUserResource;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $cartService = app(CartService::class);
        
        $cartItems = $cartService->getCartItems();
        $totalQuantity = $cartService->getTotalQuantity($cartItems);
        $totalPrice = $cartService->getTotalPrice($cartItems);

        $departments = Department::published()
                                ->with('categories')
                                ->get();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? new AuthUserResource($request->user()) : null,
                'is_admin_or_vendor' => auth()->user()?->hasAnyRole([RolesEnum::Admin, RolesEnum::Vendor]) ?: false,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'success' => [
                'message' => session('success'),
                'time' => microtime(true),
            ],
            'error' => session('error'),
            'totalQuantity' => $totalQuantity,
            'totalPrice' => $totalPrice,
            'dropdownCartItems' => $cartItems,
            'csrf_token' => csrf_token(),
            'departments' => DepartmentResource::collection($departments)->collection->toArray(),
            'appName' => config('app.name'),
            'departmentParam' => Route::currentRouteName() === 'product.byDepartment' ? $request->segment(2) : '',
            'keyword' => $request->query('keyword'),
        ];
    }
}
