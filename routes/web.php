<?php

use App\Http\Controllers\AdminProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');

Route::get('/products/{product_id}', [ProductController::class, 'show'])->name('products.show');

Route::get('/login', [AdminController::class, 'loginPage'])->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('login.submit');

Route::middleware(['auth'])->group(function () {
    Route::resource('/admin/products', AdminProductController::class)->names('admin.products');
    Route::get('/logout', [AdminController::class, 'logout'])->name('logout');
});
