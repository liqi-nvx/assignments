<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\TenantRegisterController;

Route::domain('localhost')->group(function () {
    Route::get('/register', [TenantRegisterController::class, 'showRegister'])->name('central.register');
    Route::post('/register', [TenantRegisterController::class, 'store'])->name('central.register.store');
});