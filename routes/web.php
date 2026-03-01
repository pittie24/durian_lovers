<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentConfirmationController as AdminPaymentConfirmationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentConfirmationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

// ================== GUEST ==================
Route::get('/', [GuestController::class, 'landing']);

// ================== CUSTOMER AUTH ==================
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [CustomerAuthController::class, 'login']);
Route::get('/register', [CustomerAuthController::class, 'showRegister']);
Route::post('/register', [CustomerAuthController::class, 'register']);
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

// ================== PUBLIC PRODUCT BROWSING ==================
Route::get('/produk', [ProductController::class, 'index'])->name('produk.index');
Route::get('/produk/{product}', [ProductController::class, 'show'])->name('produk.show');

// ================== CUSTOMER AREA ==================
Route::middleware(['auth', 'customer'])->group(function () {

    // Keranjang
    Route::get('/keranjang', [CartController::class, 'index'])->name('keranjang.index');

    // Route lama (tetap dibiarkan kalau ada form lain yang memakai)
    Route::post('/keranjang/tambah', [CartController::class, 'add'])->name('keranjang.tambah.legacy');

    // Route baru: /keranjang/tambah/{product} (POST)
    Route::post('/keranjang/tambah/{product}', [CartController::class, 'add'])->name('keranjang.tambah');

    Route::post('/keranjang/ubah-qty', [CartController::class, 'update'])->name('keranjang.updateQty');
    Route::post('/keranjang/hapus', [CartController::class, 'remove'])->name('keranjang.remove');

    // Checkout / Pembayaran
    Route::get('/pembayaran', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/pembayaran', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/pembayaran/return', [PaymentController::class, 'return'])->name('pembayaran.return');
    Route::get('/pembayaran/{order}', [PaymentController::class, 'show'])->name('pembayaran.show');
    Route::post('/pembayaran/{order}/lanjutkan', [PaymentController::class, 'proceed'])->name('pembayaran.proceed');
    Route::get('/pembayaran/{order}/sync', [PaymentController::class, 'syncStatus'])->name('pembayaran.sync');
    Route::get('/pembayaran/{order}/konfirmasi', [PaymentConfirmationController::class, 'show'])->name('pembayaran.confirmation.show');
    Route::post('/pembayaran/{order}/konfirmasi', [PaymentConfirmationController::class, 'store'])->name('pembayaran.confirmation.store');
    Route::get('/pembayaran/{order}/konfirmasi/ulang', [PaymentConfirmationController::class, 'resubmit'])->name('pembayaran.confirmation.resubmit');

    // Tracking + Riwayat
    Route::get('/status-pesanan', [TrackingController::class, 'index'])->name('tracking.index');
    Route::get('/status-pesanan/{order}', [TrackingController::class, 'show'])->name('tracking.show');
    Route::get('/riwayat', [HistoryController::class, 'index'])->name('riwayat.index');

    // Invoice
    Route::get('/pesanan/{order}/invoice', [InvoiceController::class, 'download'])->name('customer.invoice.download');
    Route::get('/pesanan/{order}/invoice/preview', [InvoiceController::class, 'preview'])->name('customer.invoice.preview');

    // ✅ RATING
    Route::get('/rating/{orderItem}', [RatingController::class, 'create'])->name('rating.create');
    Route::post('/rating/{orderItem}', [RatingController::class, 'store'])->name('rating.store');
});

// ================== ADMIN AUTH ==================
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// ================== ADMIN AREA ==================
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Produk
    Route::get('/produk', [AdminProductController::class, 'index'])->name('admin.produk.index');
    Route::get('/produk/tambah', [AdminProductController::class, 'create'])->name('admin.produk.create');
    Route::post('/produk', [AdminProductController::class, 'store'])->name('admin.produk.store');
    Route::get('/produk/{product}/edit', [AdminProductController::class, 'edit'])->name('admin.produk.edit');
    Route::post('/produk/{product}', [AdminProductController::class, 'update'])->name('admin.produk.update');
    Route::delete('/produk/{product}', [AdminProductController::class, 'destroy'])->name('admin.produk.destroy');

    // Konfirmasi Pembayaran
    Route::get('/payment-confirmations', [AdminPaymentConfirmationController::class, 'index'])->name('admin.payment-confirmations.index');
    Route::get('/payment-confirmations/manual/create', [AdminPaymentConfirmationController::class, 'createManual'])->name('admin.payment-confirmations.manual.create');
    Route::post('/payment-confirmations/manual', [AdminPaymentConfirmationController::class, 'storeManual'])->name('admin.payment-confirmations.manual.store');
    Route::get('/payment-confirmations/order/{order}', [AdminPaymentConfirmationController::class, 'showOrder'])->name('admin.payment-confirmations.order.show');
    Route::post('/payment-confirmations/order/{order}/status', [AdminPaymentConfirmationController::class, 'updateOrderStatusForOrder'])->name('admin.payment-confirmations.order.status');
    Route::get('/payment-confirmations/{confirmation}', [AdminPaymentConfirmationController::class, 'show'])->name('admin.payment-confirmations.show');
    Route::get('/payment-confirmations/{confirmation}/proof-image', [AdminPaymentConfirmationController::class, 'proofImage'])->name('admin.payment-confirmations.proof-image');
    Route::post('/payment-confirmations/{confirmation}/order-status', [AdminPaymentConfirmationController::class, 'updateOrderStatus'])->name('admin.payment-confirmations.order-status');
    Route::post('/payment-confirmations/{confirmation}/approve', [AdminPaymentConfirmationController::class, 'approve'])->name('admin.payment-confirmations.approve');
    Route::post('/payment-confirmations/{confirmation}/reject', [AdminPaymentConfirmationController::class, 'reject'])->name('admin.payment-confirmations.reject');
    Route::delete('/payment-confirmations/{confirmation}', [AdminPaymentConfirmationController::class, 'destroy'])->name('admin.payment-confirmations.destroy');

    // Laporan + Export
    Route::get('/laporan', [ReportController::class, 'index'])->name('admin.laporan.index');
    Route::get('/laporan/export', [ReportController::class, 'exportCsv'])->name('admin.laporan.export');
    Route::get('/laporan/export-html', [ReportController::class, 'exportHtml'])->name('admin.laporan.exportHtml');

    // Pelanggan
    Route::get('/pelanggan', [AdminCustomerController::class, 'index'])->name('admin.pelanggan.index');
    Route::get('/pelanggan/{customer}', [AdminCustomerController::class, 'show'])->name('admin.pelanggan.show');
});
