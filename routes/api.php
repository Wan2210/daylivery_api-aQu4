<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminMenuController;
use App\Http\Controllers\Api\AdminModerationController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminPartnerApplicationController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DriverOrderController;
use App\Http\Controllers\Api\DriverTrackingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartnerApplicationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SellerMenuController;
use App\Http\Controllers\Api\SellerOrderController;
use App\Http\Controllers\Api\SponsorAdController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

$registerCatalogRoutes = function (): void {
    Route::get('/ads', [SponsorAdController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product');
    Route::post('/products/{product}', [ProductController::class, 'show'])->whereNumber('product');
};

$registerCustomerRoutes = function (): void {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{product}', [CartController::class, 'update'])->whereNumber('product');
    Route::patch('/cart/{product}', [CartController::class, 'update'])->whereNumber('product');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->whereNumber('product');
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/checkout', [OrderController::class, 'store']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->whereNumber('order');
    Route::get('/seller/application', [PartnerApplicationController::class, 'seller']);
    Route::post('/seller/application', [PartnerApplicationController::class, 'submitSeller']);
    Route::get('/driver/application', [PartnerApplicationController::class, 'driver']);
    Route::post('/driver/application', [PartnerApplicationController::class, 'submitDriver']);
};

$registerSellerRoutes = function (): void {
    Route::get('/seller/menus', [SellerMenuController::class, 'index']);
    Route::post('/seller/menus', [SellerMenuController::class, 'store']);
    Route::get('/seller/menus/{menuItem}', [SellerMenuController::class, 'show'])->whereNumber('menuItem');
    Route::put('/seller/menus/{menuItem}', [SellerMenuController::class, 'update'])->whereNumber('menuItem');
    Route::patch('/seller/menus/{menuItem}', [SellerMenuController::class, 'update'])->whereNumber('menuItem');
    Route::delete('/seller/menus/{menuItem}', [SellerMenuController::class, 'destroy'])->whereNumber('menuItem');
    Route::patch('/seller/menus/{menuItem}/stock', [SellerMenuController::class, 'updateStock'])->whereNumber('menuItem');

    Route::get('/seller/orders', [SellerOrderController::class, 'index']);
    Route::get('/seller/orders/{order}', [SellerOrderController::class, 'show'])->whereNumber('order');
    Route::patch('/seller/orders/{order}/status', [SellerOrderController::class, 'updateStatus'])->whereNumber('order');
};

$registerDriverRoutes = function (): void {
    Route::post('/driver/tracking/location', [DriverTrackingController::class, 'store']);
    Route::get('/driver/tracking/location', [DriverTrackingController::class, 'latest']);
    Route::get('/driver/tracking/history', [DriverTrackingController::class, 'history']);
    Route::get('/driver/orders/available', [DriverOrderController::class, 'available']);
    Route::get('/driver/orders', [DriverOrderController::class, 'myOrders']);
    Route::get('/driver/orders/{order}', [DriverOrderController::class, 'show'])->whereNumber('order');
    Route::post('/driver/orders/{order}/accept', [DriverOrderController::class, 'accept'])->whereNumber('order');
    Route::patch('/driver/orders/{order}/status', [DriverOrderController::class, 'updateStatus'])->whereNumber('order');
};

$registerAdminRoutes = function (): void {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/categories', [AdminCategoryController::class, 'index']);
    Route::post('/admin/categories', [AdminCategoryController::class, 'store']);
    Route::get('/admin/categories/{category}', [AdminCategoryController::class, 'show'])->whereNumber('category');
    Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->whereNumber('category');
    Route::patch('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->whereNumber('category');
    Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy'])->whereNumber('category');

    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->whereNumber('user');
    Route::post('/admin/users/{user}/block', [AdminUserController::class, 'block'])->whereNumber('user');
    Route::post('/admin/users/{user}/unblock', [AdminUserController::class, 'unblock'])->whereNumber('user');
    Route::post('/admin/users/{user}/suspend', [AdminUserController::class, 'suspend'])->whereNumber('user');
    Route::post('/admin/users/{user}/unsuspend', [AdminUserController::class, 'unsuspend'])->whereNumber('user');

    Route::get('/admin/menus', [AdminMenuController::class, 'index']);
    Route::post('/admin/menus', [AdminMenuController::class, 'store']);
    Route::get('/admin/menus/{menuItem}', [AdminMenuController::class, 'show'])->whereNumber('menuItem');
    Route::put('/admin/menus/{menuItem}', [AdminMenuController::class, 'update'])->whereNumber('menuItem');
    Route::patch('/admin/menus/{menuItem}', [AdminMenuController::class, 'update'])->whereNumber('menuItem');
    Route::delete('/admin/menus/{menuItem}', [AdminMenuController::class, 'destroy'])->whereNumber('menuItem');
    Route::patch('/admin/menus/{menuItem}/stock', [AdminMenuController::class, 'updateStock'])->whereNumber('menuItem');
    Route::patch('/admin/menus/{menuItem}/sponsor', [AdminMenuController::class, 'updateSponsor'])->whereNumber('menuItem');

    Route::get('/admin/orders', [AdminOrderController::class, 'index']);
    Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->whereNumber('order');
    Route::patch('/admin/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->whereNumber('order');
    Route::post('/admin/orders/{order}/assign-driver', [AdminOrderController::class, 'assignDriver'])->whereNumber('order');
    Route::get('/admin/drivers/available', [AdminOrderController::class, 'availableDrivers']);
    Route::get('/admin/drivers/locations', [DriverTrackingController::class, 'adminIndex']);

    Route::get('/admin/sellers', [AdminModerationController::class, 'sellers']);
    Route::get('/admin/drivers', [AdminModerationController::class, 'drivers']);
    Route::get('/admin/applications/{type}', [AdminPartnerApplicationController::class, 'index'])->whereIn('type', ['seller', 'driver']);
    Route::get('/admin/applications/{type}/{application}', [AdminPartnerApplicationController::class, 'show'])->whereIn('type', ['seller', 'driver'])->whereNumber('application');
    Route::post('/admin/applications/{type}/{application}/review', [AdminPartnerApplicationController::class, 'review'])->whereIn('type', ['seller', 'driver'])->whereNumber('application');
    Route::get('/admin/staff/{user}', [AdminModerationController::class, 'show'])->whereNumber('user');
    Route::post('/admin/staff/{user}/warning', [AdminModerationController::class, 'warning'])->whereNumber('user');
    Route::post('/admin/staff/{user}/warning/reset', [AdminModerationController::class, 'resetWarning'])->whereNumber('user');
};

$registerCatalogRoutes();

Route::middleware(['auth:sanctum', 'account.status'])->group(function () use (
    $registerCustomerRoutes,
    $registerDriverRoutes,
    $registerSellerRoutes,
    $registerAdminRoutes,
): void {
    Route::middleware('role:customer')->group($registerCustomerRoutes);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
    Route::get('/orders/{order}/tracking', [DriverTrackingController::class, 'orderTracking'])->whereNumber('order');
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereUuid('id');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/register-token', [NotificationController::class, 'registerToken']);
    Route::delete('/notifications/token/{token}', [NotificationController::class, 'removeToken']);
    Route::middleware('seller')->group($registerSellerRoutes);
    Route::middleware('driver')->group($registerDriverRoutes);
    Route::middleware('role:admin')->group($registerAdminRoutes);
});

Route::prefix('v1')->group(function () use (
    $registerCatalogRoutes,
    $registerCustomerRoutes,
    $registerDriverRoutes,
    $registerSellerRoutes,
    $registerAdminRoutes,
): void {
    $registerCatalogRoutes();

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'account.status'])->group(function () use (
        $registerCustomerRoutes,
        $registerDriverRoutes,
        $registerSellerRoutes,
        $registerAdminRoutes,
    ): void {
        Route::middleware('role:customer')->group($registerCustomerRoutes);
        Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
        Route::get('/orders/{order}/tracking', [DriverTrackingController::class, 'orderTracking'])->whereNumber('order');
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->whereUuid('id');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/notifications/register-token', [NotificationController::class, 'registerToken']);
        Route::delete('/notifications/token/{token}', [NotificationController::class, 'removeToken']);
        Route::middleware('seller')->group($registerSellerRoutes);
        Route::middleware('driver')->group($registerDriverRoutes);
        Route::middleware('role:admin')->group($registerAdminRoutes);

        foreach (['addresses', 'addresis', 'auth/addresses', 'auth/addresis'] as $path) {
            Route::get('/'.$path, [AddressController::class, 'index']);
            Route::post('/'.$path, [AddressController::class, 'store']);
            Route::get('/'.$path.'/{address}', [AddressController::class, 'show'])->whereNumber('address');
            Route::put('/'.$path.'/{address}', [AddressController::class, 'update'])->whereNumber('address');
            Route::patch('/'.$path.'/{address}', [AddressController::class, 'update'])->whereNumber('address');
            Route::delete('/'.$path.'/{address}', [AddressController::class, 'destroy'])->whereNumber('address');
        }

        Route::get('/profile', [AuthController::class, 'me']);
        Route::get('/user', [AuthController::class, 'me']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::get('/auth/profile', [AuthController::class, 'me']);
        Route::get('/auth/user', [AuthController::class, 'me']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });
});
