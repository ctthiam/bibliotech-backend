<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LivreController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\EmpruntController;
use App\Http\Controllers\PenaliteController;
use App\Http\Controllers\ReservationController;

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Routes publiques pour les livres (consultation)
Route::get('/livres', [LivreController::class, 'index']);
Route::get('/livres/populaires', [LivreController::class, 'populaires']);
Route::get('/livres/nouveaux', [LivreController::class, 'nouveaux']);
Route::get('/livres/{id}', [LivreController::class, 'show']);
Route::get('/categories', [CategorieController::class, 'index']);
Route::get('/categories/{id}', [CategorieController::class, 'show']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function(){
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Livres (Bibliothécaire/Admin)
    Route::post('/livres', [LivreController::class, 'store']);
    Route::put('/livres/{id}', [LivreController::class, 'update']);
    Route::delete('/livres/{id}', [LivreController::class, 'destroy']);

    // Catégories (Bibliothécaire/Admin)
    Route::post('/categories', [CategorieController::class, 'store']);
    Route::put('/categories/{id}', [CategorieController::class, 'update']);
    Route::delete('/categories/{id}', [CategorieController::class, 'destroy']);

     // Emprunts
    Route::get('/emprunts', [EmpruntController::class, 'index']);
    Route::post('/emprunts', [EmpruntController::class, 'store']);
    Route::post('/emprunts/{id}/prolonger', [EmpruntController::class, 'prolonger']);
    Route::post('/emprunts/{id}/retourner', [EmpruntController::class, 'retourner']);
    
    // Routes spécifiques lecteur
    Route::get('/mes-emprunts', [EmpruntController::class, 'mesEmprunts']);
    Route::get('/historique-emprunts', [EmpruntController::class, 'historique']);
    
    // Statistiques (Bibliothécaire/Admin)
    Route::get('/emprunts/statistiques', [EmpruntController::class, 'statistiques']);

    Route::post('emprunts/par-livre', [EmpruntController::class, 'emprunterParLivre']);

        // Routes Pénalités
    Route::prefix('penalites')->group(function () {
        // Accessible par tous les utilisateurs connectés
        Route::get('/', [PenaliteController::class, 'index']);
        Route::get('/statistiques', [PenaliteController::class, 'statistiques']);
        Route::get('/{id}', [PenaliteController::class, 'show']);
        
        // Réservé Admin/Bibliothécaire
        Route::post('/{id}/payer', [PenaliteController::class, 'marquerPayee']);
        
        // Réservé Admin uniquement
        Route::post('/{id}/annuler', [PenaliteController::class, 'annuler']);
        
        // Calcul automatique (à appeler via CRON ou manuellement)
        Route::post('/calculer', [PenaliteController::class, 'calculerPenalites']);
    });

    // Routes Réservations
    Route::prefix('reservations')->group(function () {
        // Accessible par tous les utilisateurs connectés
        Route::get('/', [ReservationController::class, 'index']);
        Route::get('/statistiques', [ReservationController::class, 'statistiques']);
        Route::get('/{id}', [ReservationController::class, 'show']);
        
        // Créer et annuler (Lecteur)
        Route::post('/', [ReservationController::class, 'store']);
        Route::post('/{id}/annuler', [ReservationController::class, 'cancel']);
        
        // Réservé Admin/Bibliothécaire
        Route::post('/{id}/disponible', [ReservationController::class, 'marquerDisponible']);
        Route::post('/{id}/expirer', [ReservationController::class, 'marquerExpiree']);
    });

});