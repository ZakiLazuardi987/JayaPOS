<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\POSController; // Tambahkan controller baru untuk POS

// ==========================================
// LANDING PAGE (Bisa diakses siapa saja)
// ==========================================
Route::get('/', function () {
    return view('landing');
})->name('home');

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

        // AJAX: Detail produk (modifier tergroup)
        Route::get('/product-detail/{id}', [POSController::class, 'getProductDetail'])->name('productDetail');

        // Action AJAX untuk Favorit (Tetap diperlukan)
        Route::post('/favorite/add', [POSController::class, 'addFavorite'])->name('addFavorite');
        Route::post('/favorite/remove', [POSController::class, 'removeFavorite'])->name('removeFavorite');
        
    });
});

Route::get('/logout', function () {
    Illuminate\Support\Facades\Auth::logout();
    session()->flush(); // Hapus semua data session termasuk active_outlet
    return redirect('/'); // Kembalikan ke landing page
})->name('logout');