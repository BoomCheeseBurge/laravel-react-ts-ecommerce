<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use Inertia\Response;
use App\Enums\RolesEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
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
    public function store(LoginRequest $request): HttpFoundationResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /**
         * @var App\Models\User $user
         */
        $user = auth()->user();

        if ($user->hasAnyRole([RolesEnum::Admin, RolesEnum::Vendor])) {

            // Redirect to outside of the Inertia page which is Filament's admin panel page
            return Inertia::location(route('filament.admin.pages.dashboard'));
        }

        /**
         * Absolute false means to generate URL relative to the current URL
         * In other words, the full base URL is not generated (e.g., 'http://laravel-react-ts-ecommerce.test/dashboard') 
         * Instead, it will only be '/dashboard'
         * The full base URL will come from the redirect intended route.
         */
        return redirect()->intended(route('dashboard', absolute: false));
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
