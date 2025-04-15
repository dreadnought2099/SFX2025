<?php

use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

// For verifying the email registered
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');
});


Route::get('/', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register');


Route::group(['middleware' => 'auth', 'verified'], function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products/create', [ProductController::class, 'create'])->name('products.create');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});
