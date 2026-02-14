<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GuestController::class, 'landing']);

Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login']);
Route::get('/register', [CustomerAuthController::class, 'showRegister']);
Route::post('/register', [CustomerAuthController::class, 'register']);
Route::post('/logout', [CustomerAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/produk', [ProductController::class, 'index']);
    Route::get('/produk/{product}', [ProductController::class, 'show']);

    Route::get('/keranjang', [CartController::class, 'index']);
    Route::post('/keranjang/tambah', [CartController::class, 'add']);
    Route::post('/keranjang/ubah-qty', [CartController::class, 'update']);
    Route::post('/keranjang/hapus', [CartController::class, 'remove']);

    Route::get('/checkout', [CheckoutController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'store']);

    Route::get('/pembayaran/{order}', [PaymentController::class, 'show']);
    Route::post('/pembayaran/{order}/lanjut', [PaymentController::class, 'proceed']);

    Route::get('/tracking/{order}', [TrackingController::class, 'show']);
    Route::get('/riwayat', [HistoryController::class, 'index']);

    Route::get('/rating/{orderItem}', [RatingController::class, 'create']);
    Route::post('/rating/{orderItem}', [RatingController::class, 'store']);
});

Route::post('/payment/midtrans/webhook', [PaymentController::class, 'webhook']);

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/produk', [AdminProductController::class, 'index']);
    Route::get('/produk/tambah', [AdminProductController::class, 'create']);
    Route::post('/produk', [AdminProductController::class, 'store']);
    Route::get('/produk/{product}/edit', [AdminProductController::class, 'edit']);
    Route::post('/produk/{product}', [AdminProductController::class, 'update']);
    Route::delete('/produk/{product}', [AdminProductController::class, 'destroy']);

    Route::get('/pesanan', [AdminOrderController::class, 'index']);
    Route::get('/pesanan/{order}', [AdminOrderController::class, 'show']);
    Route::post('/pesanan/{order}/status', [AdminOrderController::class, 'updateStatus']);

    Route::get('/laporan', [ReportController::class, 'index']);
    Route::get('/pelanggan', [AdminCustomerController::class, 'index']);
    Route::get('/pelanggan/{customer}', [AdminCustomerController::class, 'show']);
});
