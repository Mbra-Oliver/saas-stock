<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\APi\WarehouseController;

// 📌 Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // Créer un compte
    Route::post('/login', [AuthController::class, 'login']);       // Se connecter
});

// 🔒 Routes protégées par Sanctum (authentification requise)

Route::post('/logout', [AuthController::class, 'logout']);     // Se déconnecter
Route::get('/me', [AuthController::class, 'me']);              // Infos utilisateur connecté

Route::middleware('auth:sanctum')->group(function () {
    // Routes pour les entrepôts
    Route::apiResource('warehouses', WarehouseController::class);

    // Routes additionnelles
    Route::get('companies/{company}/warehouses', [WarehouseController::class, 'byCompany']);
    Route::patch('warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus']);
});
