<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\ReportController;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('tenant.login');
        Route::post('/login', [AuthController::class, 'login'])->name('tenant.login.submit');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('tenant.register');
        Route::post('/register', [AuthController::class, 'register'])->name('tenant.register.submit');
    });

    // 租户客端：受 Sanctum / Session 保护路由
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');
        Route::get('/profile', [AuthController::class, 'editProfile'])->name('tenant.profile');
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('tenant.profile.update');

        Route::resource('customers', CustomerController::class);
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/buy', [ProductController::class, 'buy'])->name('products.buy');
        
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('sales-report', [ReportController::class, 'salesReport'])->name('reports.sales');
    });
});