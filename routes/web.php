<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AccountController as AdminAccountController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonationUpdateController;
use App\Http\Controllers\Admin\MarketplaceController as AdminMarketplaceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogPostsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\MarketplaceProductController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TEMPORARY
|--------------------------------------------------------------------------
*/
Route::get('/test/landing', function () {
    return view('test.testLandingPage');
});

Route::get('/debug-boot', function () {
    try {
        // Attempt to resolve a core service to trigger the error
        app('view');

        return 'View service resolved successfully!';
    } catch (Throwable $e) {
        // Log the full exception to Vercel's STDERR
        Log::error('BOOT DEBUG: '.$e->getMessage());
        Log::error('BOOT DEBUG TRACE: '.$e->getTraceAsString());

        // Also dump it to the browser output
        return response()->make(
            '<pre>'.e($e).'</pre>',
            500
        )->header('Content-Type', 'text/html');
    }
});

// Publicly Accessible Routes (checking if admin to redirect)
Route::middleware(['not_admin'])->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::get('/about', function () {
        return view('about');
    });

    Route::get('/contact', function () {
        return view('contact');
    });
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Accounts Management
    Route::get('/accounts', [AdminAccountController::class, 'index'])->name('accounts.index');
    Route::patch('/accounts/{user}/role', [AdminAccountController::class, 'updateRole'])->name('accounts.updateRole');
    Route::delete('/accounts/{user}', [AdminAccountController::class, 'destroy'])->name('accounts.destroy');

    // Marketplace Management
    Route::get('/marketplace', [AdminMarketplaceController::class, 'index'])->name('marketplace.index');
    // Using existing controllers for store/update/destroy since logic is identical
    Route::get('/marketplace/create', [MarketplaceProductController::class, 'create'])->name('marketplace.create');
    Route::post('/marketplace', [MarketplaceProductController::class, 'store'])->name('marketplace.store');
    Route::get('/marketplace/{id}/edit', [MarketplaceProductController::class, 'edit'])->name('marketplace.edit');
    Route::put('/marketplace/{id}', [MarketplaceProductController::class, 'update'])->name('marketplace.update');
    Route::delete('/marketplace/{id}', [MarketplaceProductController::class, 'destroy'])->name('marketplace.destroy');

    // Blog Management
    Route::get('/blogs', [AdminBlogController::class, 'index'])->name('blogs.index');
    // Using existing controllers for store/update/destroy
    Route::get('/blogs/create', [BlogPostsController::class, 'create'])->name('blogs.create');
    Route::post('/blogs', [BlogPostsController::class, 'store'])->name('blogs.store');
    Route::get('/blogs/{id}/edit', [BlogPostsController::class, 'edit'])->name('blogs.edit');
    Route::put('/blogs/{id}', [BlogPostsController::class, 'update'])->name('blogs.update');
    Route::delete('/blogs/{id}', [BlogPostsController::class, 'destroy'])->name('blogs.destroy');

    // Donations Management
    Route::get('/donations', [DonationController::class, 'adminIndex'])->name('donations.index');
    Route::delete('/donations/{donation}', [DonationController::class, 'adminDestroy'])->name('donations.destroy');
    Route::get('/donations/{donation}/updates', [DonationUpdateController::class, 'index'])->name('donations.updates.index');
    Route::get('/donations/{donation}/updates/create', [DonationUpdateController::class, 'create'])->name('donations.updates.create');
    Route::post('/donations/{donation}/updates', [DonationUpdateController::class, 'store'])->name('donations.updates.store');

    // Payments Management
    Route::get('/payments/test', [PaymentController::class, 'test'])->name('payments.test');
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
});

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-limit');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth-limit');
});

// Authenticated Normal User Routes
Route::middleware(['auth'])->group(function () {
    Route::middleware(['not_admin'])->group(function () {
        // Public Blog
        Route::get('/blog', [BlogPostsController::class, 'index'])->name('blog.index');
        Route::get('/blog/{id}', [BlogPostsController::class, 'show'])->name('blog.show');

        // Public Marketplace
        Route::get('/marketplace', [MarketplaceProductController::class, 'index'])->name('marketplace.index');
        Route::get('/marketplace/success', function () {
            $latestPayment = \App\Models\Payment::where('user_id', auth()->id())
                ->where('payment_type', 'marketplace')
                ->where('status', 'pending')
                ->latest('id')
                ->first();
            if ($latestPayment && str_starts_with($latestPayment->snap_token ?? '', 'DEMO-')) {
                $latestPayment->update(['status' => 'settlement']);
                // Demo payment is paid: decrease stock & increase sold count
                \App\Services\MarketplaceOrderService::fulfill($latestPayment);
            }

            session()->forget(['cart', 'coupon']);

            return view('marketplace_success');
        })->name('marketplace.success');

        // Donation
        Route::get('/donation', [DonationController::class, 'index'])->name('donation.index');
        Route::get('/donation/{donation}/updates', [DonationController::class, 'updates'])->name('donation.updates');
        Route::post('/donation', [DonationController::class, 'store'])->middleware('throttle:donation-limit')->name('donation.store');
        Route::get('/donation/success', [DonationController::class, 'success'])->name('donation.success');

        // Account Page CRUD
        Route::get('/account', [AccountController::class, 'index'])->name('account.index');
        Route::put('/account', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/account', [AccountController::class, 'destroy'])->name('account.destroy');

        // Cart & Marketplace Checkout
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/cart/sync',[CartController::class, 'sync'])->name('cart.sync');
        Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
        Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
        Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::post('/cart/process', [CartController::class, 'process'])->middleware('throttle:checkout-limit')->name('cart.process');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Midtrans Webhook Notification
Route::post('/payment/notification', [PaymentWebhookController::class, 'handleNotification'])->name('payment.notification');
