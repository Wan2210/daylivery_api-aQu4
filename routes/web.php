<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCategoryManagementController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminPartnerApplicationController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminSponsorAdController;
use App\Http\Controllers\AdminUserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('admin.dashboard');
    Route::get('/users', [AdminUserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/users/create', [AdminUserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [AdminUserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/users/{user}/edit', [AdminUserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminUserManagementController::class, 'update'])->name('admin.users.update');
    Route::get('/categories', [AdminCategoryManagementController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [AdminCategoryManagementController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{category}', [AdminCategoryManagementController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{category}', [AdminCategoryManagementController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('/applications/{type}', [AdminPartnerApplicationController::class, 'index'])->whereIn('type', ['seller', 'driver'])->name('admin.applications.index');
    Route::get('/applications/{type}/{application}', [AdminPartnerApplicationController::class, 'show'])->whereIn('type', ['seller', 'driver'])->whereNumber('application')->name('admin.applications.show');
    Route::post('/applications/{type}/{application}/review', [AdminPartnerApplicationController::class, 'review'])->whereIn('type', ['seller', 'driver'])->whereNumber('application')->name('admin.applications.review');
    Route::get('/applications/{type}/{application}/documents/{document}', [AdminPartnerApplicationController::class, 'document'])->whereIn('type', ['seller', 'driver'])->whereNumber('application')->whereNumber('document')->name('admin.applications.document');
    Route::get('/menus', [AdminMenuController::class, 'index'])->name('admin.menus.index');
    Route::get('/menus/create', [AdminMenuController::class, 'create'])->name('admin.menus.create');
    Route::post('/menus', [AdminMenuController::class, 'store'])->name('admin.menus.store');
    Route::patch('/menus/{menuItem}/sponsor', [AdminMenuController::class, 'updateSponsor'])->name('admin.menus.sponsor');
    Route::get('/ads', [AdminSponsorAdController::class, 'index'])->name('admin.ads.index');
    Route::post('/ads', [AdminSponsorAdController::class, 'store'])->name('admin.ads.store');
    Route::put('/ads/{sponsorAd}', [AdminSponsorAdController::class, 'update'])->name('admin.ads.update');
    Route::delete('/ads/{sponsorAd}', [AdminSponsorAdController::class, 'destroy'])->name('admin.ads.destroy');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('admin.settings');
    Route::get('/orders/create', [AdminDashboardController::class, 'create'])->name('admin.orders.create');
    Route::post('/orders', [AdminDashboardController::class, 'store'])->name('admin.orders.store');
    Route::patch('/orders/{order}/status', [AdminDashboardController::class, 'updateStatus'])->name('admin.orders.status');
    Route::get('/orders/export', [AdminDashboardController::class, 'export'])->name('admin.orders.export');
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('admin.logout');
});
