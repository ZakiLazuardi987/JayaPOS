<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\POSController;

// ==========================================
// LANDING PAGE (Bisa diakses siapa saja)
// ==========================================
Route::get('/', function () {
    return view('landing');
})->name('home');

// ==========================================
// API ROUTES (No CSRF)
// ==========================================
Route::post('/api/midtrans/webhook', [\App\Http\Controllers\MidtransWebhookController::class, 'handle']);

// ==========================================
// GUEST ROUTES (Hanya untuk yang BELUM login)
// ==========================================
Route::middleware(['guest'])->group(function () {
    // Register
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Login (Step 1 & 2)
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login/check-account', [LoginController::class, 'checkAccount'])->name('login.check');
    Route::post('/login/verify-password', [LoginController::class, 'verifyPassword'])->name('login.verify');
});

// ==========================================
// AUTH ROUTES (Hanya untuk yang SUDAH login)
// ==========================================
Route::middleware(['auth'])->group(function () {
    // Login (Step 3)
    Route::post('/login/select-outlet', [LoginController::class, 'selectOutlet'])->name('login.outlet');
    
    // --- GRUP ROUTE POS ---
    // Semua URL di sini akan otomatis diawali dengan /pos (contoh: /pos/library)
    Route::prefix('pos')->name('pos.')->group(function () {
        
        // Tampilan Utama (Tab Favorit)
        Route::get('/', [POSController::class, 'index'])->name('favorit');
        
        // Tampilan Library
        Route::get('/library', [POSController::class, 'library'])->name('library');
        
        // Tampilan Custom (Kalkulator)
        Route::get('/custom', [POSController::class, 'custom'])->name('custom');

        // Tampilan Denah Meja
        Route::get('/denah-meja', [POSController::class, 'denahMeja'])->name('denah-meja');

        // Tampilan Aktivitas (Histori Transaksi)
        Route::get('/aktivitas', [POSController::class, 'aktivitas'])->name('aktivitas');
        Route::post('/orders/{id}/refund', [POSController::class, 'refundOrder'])->name('refundOrder');

        // Tampilan Inventori (Stok)
        Route::get('/inventori', [POSController::class, 'inventori'])->name('inventori');
        Route::post('/inventori/update', [POSController::class, 'updateInventori'])->name('updateInventori');

        // AJAX: Detail produk (modifier tergroup)
        Route::get('/product-detail/{id}', [POSController::class, 'getProductDetail'])->name('productDetail');

        // Action AJAX untuk Favorit (Tetap diperlukan)
        Route::post('/favorite/add', [POSController::class, 'addFavorite'])->name('addFavorite');
        Route::post('/favorite/remove', [POSController::class, 'removeFavorite'])->name('removeFavorite');
        
        // AJAX: Simpan Bill (Order Pending + Table Occupied)
        Route::post('/orders/save-bill', [POSController::class, 'saveBill'])->name('saveBill');

        // AJAX: Daftar Bill (Pending Orders)
        Route::get('/orders/pending-bills', [POSController::class, 'getPendingBills'])->name('pendingBills');

        // AJAX: Detail Order (follow-up bill)
        Route::get('/orders/{id}/detail', [POSController::class, 'getOrderDetail'])->name('orderDetail');

        // AJAX: Fetch Pending Order by Pickup Code (Kiosk/CRM integration)
        Route::get('/orders/pickup/{code}', [POSController::class, 'getOrderByPickupCode'])->name('getOrderByPickupCode');

        // AJAX: Update Meja Order Pending
        Route::patch('/orders/{id}/update-table', [POSController::class, 'updateOrderTable'])->name('updateOrderTable');

        // AJAX: Checkout Cash
        Route::post('/checkout/cash', [POSController::class, 'checkoutCash'])->name('checkoutCash');

        // AJAX: Checkout Pisah Bayar (Split Payment)
        Route::post('/checkout/split', [POSController::class, 'checkoutSplit'])->name('checkoutSplit');

        // AJAX: Cancel temp QRIS order (cleanup after split payment finalize)
        Route::post('/order/{orderId}/cancel-temp', [POSController::class, 'cancelTempOrder'])->name('cancelTempOrder');

        // Cetak Struk (PDF)
        Route::get('/order/{orderId}/print', [POSController::class, 'cetakStruk'])->name('printStruk');

        // Data Struk (JSON untuk Bluetooth ESC/POS Printer)
        Route::get('/order/{orderId}/struk-data', [POSController::class, 'getStrukData'])->name('getStrukData');

        // AJAX: Checkout QRIS
        Route::post('/checkout/qris', [POSController::class, 'checkoutQris'])->name('checkoutQris');

        // AJAX: Polling Status QRIS
        Route::get('/checkout/qris/{orderId}/status', [POSController::class, 'checkQrisStatus']);

        // AJAX: Cek Customer (Loyalty)
        Route::get('/customers/check', [POSController::class, 'checkCustomer']);
        
        // AJAX: Search Members (Search Modal)
        Route::get('/customers/search', [POSController::class, 'searchMembers']);
    });
});

Route::get('/logout', function () {
    Illuminate\Support\Facades\Auth::logout();
    session()->flush(); // Hapus semua data session termasuk active_outlet
    return redirect('/'); // Kembalikan ke landing page
})->name('logout');
