<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FineController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route API — Sistem Manajemen Perpustakaan
|--------------------------------------------------------------------------
*/

// Route publik — login
Route::post('/login', [AuthController::class, 'login']);

// Route yang dilindungi Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/loan-report', [DashboardController::class, 'loanReport']);
    Route::get('/dashboard/loan-report/export/{format}', [DashboardController::class, 'exportLoanReport']);

    // Kategori — CRUD
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);

    // Item — CRUD + import/export
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::post('/items/import', [ItemController::class, 'import']);
    Route::get('/items/export/{format}', [ItemController::class, 'export']);

    // Anggota — CRUD
    Route::get('/members', [MemberController::class, 'index']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::put('/members/{member}', [MemberController::class, 'update']);

    // Peminjaman
    Route::get('/loans', [LoanController::class, 'index']);
    Route::post('/loans', [LoanController::class, 'store']);
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnLoan']);

    // Denda
    Route::get('/fines', [FineController::class, 'index']);
    Route::post('/fines/{fine}/pay', [FineController::class, 'pay']);

    // Route khusus admin — operasi destruktif
    Route::middleware('admin')->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::delete('/items/{item}', [ItemController::class, 'destroy']);
        Route::delete('/members/{member}', [MemberController::class, 'destroy']);
    });
});
