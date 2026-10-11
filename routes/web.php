<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->user()?->isStaff()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent.');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::middleware('can:access-active-account')->group(function () {
    Route::get('/products', function () {
        $search = request()->string('search')->trim()->toString();
        $paperSize = request()->string('paper_size')->trim()->toString();
        $paperFinish = request()->string('paper_finish')->trim()->toString();
        $products = Product::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')))
            ->when($paperSize !== '', fn ($query) => $query->where('paper_size', $paperSize))
            ->when($paperFinish !== '', fn ($query) => $query->where('paper_finish', $paperFinish))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('store.products', [
            'products' => $products,
            'paperSizes' => Product::where('is_active', true)->whereNotNull('paper_size')->distinct()->orderBy('paper_size')->pluck('paper_size'),
            'paperFinishes' => Product::where('is_active', true)->whereNotNull('paper_finish')->distinct()->orderBy('paper_finish')->pluck('paper_finish'),
        ]);
    })->name('products.index');

    Route::get('/products/{product}', function (Product $product) {
        abort_unless($product->is_active, 404);

        return view('store.product-show', compact('product'));
    })->name('products.show');

    Route::get('/services', function () {
        $search = request()->string('search')->trim()->toString();
        $services = Service::query()
            ->where('is_active', true)
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('store.services', [
            'services' => $services,
        ]);
    })->name('services');

    Route::get('/services/{service}', function (Service $service) {
        abort_unless($service->is_active, 404);

        return view('store.service-show', compact('service'));
    })->name('services.show');

    Route::middleware('can:access-customer')->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/artwork', [OrderController::class, 'downloadArtwork'])->name('orders.artwork');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/orders/{order}/accept-quote', [OrderController::class, 'acceptQuote'])->name('orders.accept-quote');
    });

    Route::get('/account', [AccountController::class, 'index'])->name('account.dashboard');
    Route::get('/account/edit', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    });

    Route::prefix('admin')->name('admin.')->middleware('can:manage-shop')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/artwork', [AdminOrderController::class, 'downloadArtwork'])->name('orders.artwork');
        Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
        Route::middleware('can:access-admin')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::resource('users', AdminUserController::class)->except(['show', 'destroy']);
        });
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
