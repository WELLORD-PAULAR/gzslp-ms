<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ServiceController;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->user()?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/products', function () {
        return view('store.products', [
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    })->name('products.index');

    Route::get('/services', function () {
        return view('store.services', [
            'services' => Service::where('is_active', true)->orderBy('name')->get(),
        ]);
    })->name('services');

    Route::view('/cart', 'cart.index')->name('cart');

    Route::get('/account', function () {
        return view('account.dashboard');
    })->name('account.dashboard');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::resource('services', ServiceController::class)->except('show');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
