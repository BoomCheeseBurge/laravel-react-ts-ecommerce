<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Inertia\Response;
use App\Enums\RolesEnum;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Support\Timebox;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Requests\Auth\LoginRequest;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, CartService $cartService): HttpFoundationResponse
    {
        return (new Timebox)->call(function ($timebox) use ($request, $cartService) {

            $request->authenticate($timebox);

            $request->session()->regenerate();

            /**
             * @var App\Models\User $user
             */
            $user = auth()->user();

            // Check if the user is an admin or a vendor
            if ($user->hasAnyRole([RolesEnum::Admin, RolesEnum::Vendor])) {

                // Admin and vendor can be regular buyers
                $cartService->moveCartItemsToDatabase($user->id);

                // Redirect to outside of the Inertia page which is Filament's admin panel page
                return Inertia::location(route('filament.admin.pages.dashboard'));
            }

            // Move the cart items from cookies to the database (if any)
            $cartService->moveCartItemsToDatabase($user->id);

        // $endTime = microtime(true);
        // $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Log::info('Authentication successful. Execution time: ' . $executionTime . ' milliseconds.');
        
        /**
         * Absolute false means to generate URL relative to the current URL
         * In other words, the full base URL is not generated (e.g., 'http://laravel-react-ts-ecommerce.test/home') 
         * Instead, it will only be '/home'
         * The full base URL will come from the redirect intended route.
         */
            return redirect()->intended(route('home', absolute: false));
        }, 400 * 1000);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
