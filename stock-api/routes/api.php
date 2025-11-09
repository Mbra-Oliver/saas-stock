<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AIController;
use Illuminate\Support\Facades\Route;

// Public routes (sans authentification)
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (avec authentification Sanctum)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // ============================================
    // AUTH ROUTES
    // ============================================
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('password', [AuthController::class, 'changePassword']);

    // ============================================
    // PRODUCTS ROUTES
    // ============================================
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('products/search/{query}', [ProductController::class, 'search']);
    Route::post('products/{product}/image', [ProductController::class, 'uploadImage']);
    Route::apiResource('products', ProductController::class);

    // ============================================
    // STOCK MANAGEMENT ROUTES
    // ============================================
    Route::get('stocks', [StockController::class, 'index']);
    Route::post('stocks/in', [StockController::class, 'stockIn']);
    Route::post('stocks/out', [StockController::class, 'stockOut']);
    Route::post('stocks/transfer', [StockController::class, 'transfer']);
    Route::post('stocks/adjust', [StockController::class, 'adjust']);
    Route::get('stocks/movements', [StockController::class, 'movements']);
    Route::get('stocks/low-stock', [StockController::class, 'lowStock']);
    Route::get('stocks/product/{product_id}', [StockController::class, 'productStock']);

    // ============================================
    // ORDERS ROUTES
    // ============================================
    Route::get('orders/stats', [OrderController::class, 'stats']);
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::put('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus']);
    Route::apiResource('orders', OrderController::class);

    // ============================================
    // WAREHOUSES ROUTES
    // ============================================
    Route::apiResource('warehouses', WarehouseController::class);

    // ============================================
    // CATEGORIES ROUTES
    // ============================================
    Route::apiResource('categories', CategoryController::class);

    // ============================================
    // SUPPLIERS ROUTES
    // ============================================
    Route::apiResource('suppliers', SupplierController::class);

    // ============================================
    // DASHBOARD ROUTES
    // ============================================
    Route::get('dashboard/overview', [DashboardController::class, 'overview']);
    Route::get('dashboard/sales-chart', [DashboardController::class, 'salesChart']);
    Route::get('dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('dashboard/recent-movements', [DashboardController::class, 'recentMovements']);
    Route::get('dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('dashboard/stock-value', [DashboardController::class, 'stockValue']);
    Route::get('dashboard/inventory-turnover', [DashboardController::class, 'inventoryTurnover']);

    // ============================================
    // AI & ANALYTICS ROUTES
    // ============================================
    Route::post('ai/forecast', [AIController::class, 'forecast']);
    Route::post('ai/assistant', [AIController::class, 'assistant']);
    Route::post('ai/ocr', [AIController::class, 'ocr']);
});
