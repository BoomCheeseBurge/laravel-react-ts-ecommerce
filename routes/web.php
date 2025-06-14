<?php

use App\Enums\RolesEnum;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
// use Illuminate\Mail\Markdown;

// Route::get('/markdown/mail', function () {
//     $markdown = new Markdown(view(), config('mail.markdown'));

//     return $markdown->render('mail.vendor_payout');
// })->name('test.mail.markdown');

// Route::get('/markdown/mail', function () {
//     $markdown = new Markdown(view(), config('mail.markdown'));

//     return $markdown->render('mail.vendor_application_status', [
//         'status' => "approved",
//     ]);
// })->name('test.mail.markdown');

/**
 * 
 *   ______ _     _ _______ _______ _______
 *   |  ____ |     | |______ |______    |   
 *   |_____| |_____| |______ ______|    |   
 *
 */
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/department/{department:slug}', [ProductController::class, 'byDepartment'])->name('product.byDepartment');

Route::controller(CartController::class)->group(function () {

    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/add/{product}', 'store')->name('cart.store');
    Route::put('/cart/{product}', 'update')->name('cart.update');
    Route::put('/cart/{product}', 'checkoutLater')->name('cart.checkout.later');
    Route::delete('/cart/{product}', 'destroy')->name('cart.destroy');
});

/**
 * 
 *   __  ___  __     __   ___ 
 *  /__`  |  |__) | |__) |__  
 *  .__/  |  |  \ | |    |___ 
 *
 */
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

/**
 * 
 *        ___       __   __   __  
 *  \  / |__  |\ | |  \ /  \ |__) 
 *   \/  |___ | \| |__/ \__/ |  \ 
 *
 */
Route::get('/vendor/store/{vendor:store_name}', [VendorController::class, 'profile'])->name('vendor.profile');

/**
 * 
 *   _______ _     _ _______ _     _
 *   |_____| |     |    |    |_____|
 *   |     | |_____|    |    |     |
 *
 */
Route::middleware('auth')->group(function () {

    /**
     * 
     *   __   __   __   ___         ___ 
     *  |__) |__) /  \ |__  | |    |__  
     *  |    |  \ \__/ |    | |___ |___ 
     *
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * 
     *  _    _ _______  ______ _____ _______ _____ _______ ______ 
     *   \  /  |______ |_____/   |   |______   |   |______ |     \
     *    \/   |______ |    \_ __|__ |       __|__ |______ |_____/
     *
     */
    Route::middleware('verified')->group(function () {

        /**
         * 
         *   __        __  ___ 
         *  /  `  /\  |__)  |  
         *  \__, /~~\ |  \  |  
         *
         */
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

        /**
         * 
         *        ___       __   __   __  
         *  \  / |__  |\ | |  \ /  \ |__) 
         *   \/  |___ | \| |__/ \__/ |  \ 
         *
         */
        Route::post('/vendor/register', [VendorController::class, 'requestVendor'])->name('vendor.register');
        Route::post('/vendor/setup', [VendorController::class, 'store'])->name('vendor.store');

        /**
         * 
         *   __  ___  __     __   ___ 
         *  /__`  |  |__) | |__) |__  
         *  .__/  |  |  \ | |    |___ 
         *
         */
        Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('/stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');
        Route::post('/stripe/connect', [StripeController::class, 'connect'])->middleware('role:' . RolesEnum::Vendor->value)->name('stripe.connect');

        /**
         * 
         *    __   __   __   ___  __  
         *   /  \ |__) |  \ |__  |__) 
         *   \__/ |  \ |__/ |___ |  \ 
         *
         */
        Route::get('/orders', [OrderController::class, 'index'])->name('order.index');

        Route::post('/status/update', [OrderController::class, 'update'])->name('order.update');
    });
});

require __DIR__.'/auth.php';
