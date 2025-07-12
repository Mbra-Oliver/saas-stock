<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

// 📌 Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // Créer un compte
    Route::post('/login', [AuthController::class, 'login']);       // Se connecter
});

// 🔒 Routes protégées par Sanctum (authentification requise)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);     // Se déconnecter
    Route::get('/me', [AuthController::class, 'me']);              // Infos utilisateur connecté
});
