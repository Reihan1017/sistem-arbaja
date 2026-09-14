<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TB. AR Baja Steelindo — Routes
|--------------------------------------------------------------------------
| Tempelkan seluruh isi blok ini ke routes/web.php project Laravel kamu
| (timpa isi lama yang berhubungan dengan documents/invoices).
*/

// ===== Halaman utama =====
Route::get('/', function () {
    return redirect()->route('documents.index', ['type' => 'surat_keluar']);
});

// ===== Login / Logout (tidak butuh auth) =====
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ===== Semua rute di bawah ini WAJIB login =====
Route::middleware('auth')->group(function () {

    // Workspace 1: Surat menyurat umum
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create/{type}', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::get('/{document}/print', [DocumentController::class, 'print'])->name('print');

        // Hapus data: khusus admin
        Route::delete('/{document}', [DocumentController::class, 'destroy'])
            ->middleware('admin')->name('destroy');
    });

    // Workspace 2: Penagihan & pengiriman
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::patch('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('updateStatus');
        Route::get('/{invoice}/print/invoice', [InvoiceController::class, 'printInvoice'])->name('print.invoice');
        Route::get('/{invoice}/print/do', [InvoiceController::class, 'printDeliveryOrder'])->name('print.do');

        // Hapus data: khusus admin
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])
            ->middleware('admin')->name('destroy');
    });

    // Kelola User: khusus admin
    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
