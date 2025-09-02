<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\PersonnelController;
use App\Http\Controllers\Api\EleveController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\AnneeScolaireController;
use App\Http\Controllers\Api\NiveauController;
use App\Http\Controllers\Api\ProgrammeController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\ClasseController;
use App\Http\Controllers\Api\InscriptionEleveController;
use App\Http\Controllers\Api\InscriptionFinanciereController;
use App\Http\Controllers\Api\EmploiDuTempsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Vérification de santé et version
Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/version', fn () => response()->json(['app' => config('app.name'), 'laravel' => app()->version()]));

// Routes publiques (authentification non requise)
Route::post('/auth/login', [AuthController::class, 'login']);

// Routes protégées (authentification requise)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentification
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Gestion des utilisateurs
    Route::apiResource('users', UserController::class);
    Route::get('/users/profiles', [UserController::class, 'profiles']);
    Route::post('/users/{id}/toggle-lock', [UserController::class, 'toggleLock']);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    
    // ========================================
    // GESTION DES COMPTES UTILISATEURS
    // ========================================
    
    // Création de comptes pour les différents types d'utilisateurs
    Route::post('/users/create-eleve-account', [UserController::class, 'createEleveAccount']);
    Route::post('/users/create-parent-account', [UserController::class, 'createParentAccount']);
    Route::post('/users/create-personnel-account', [UserController::class, 'createPersonnelAccount']);
    
    // Gestion des comptes existants
    Route::post('/users/{id}/reset-password-auto', [UserController::class, 'resetPasswordAuto']);
    Route::post('/users/{id}/toggle-account-status', [UserController::class, 'toggleAccountStatus']);
    
    // Liste des utilisateurs sans compte
    Route::get('/users/without-account', [UserController::class, 'getUsersWithoutAccount']);

    // ========================================
    // MODULE 2 : ADMINISTRATION & PARAMÉTRAGE
    // ========================================

    // Gestion des profils
    Route::get('/profils/actifs', [ProfilController::class, 'actifs']);
    Route::post('/profils/{id}/toggle-status', [ProfilController::class, 'toggleStatus']);
    Route::apiResource('profils', ProfilController::class);

    // Gestion des années scolaires
    Route::apiResource('annees-scolaires', AnneeScolaireController::class);
    Route::get('/annees-scolaires/courante', [AnneeScolaireController::class, 'current']);

    // Gestion des niveaux
    Route::get('/niveaux/actifs', [NiveauController::class, 'actifs']);
    Route::get('/niveaux/programme/{programme_id}', [NiveauController::class, 'byProgramme']);
    Route::apiResource('niveaux', NiveauController::class);

    // Gestion des programmes
    Route::get('/programmes/actifs', [ProgrammeController::class, 'actifs']);
    Route::apiResource('programmes', ProgrammeController::class);

    // Gestion des matières
    Route::get('/matieres/actives', [MatiereController::class, 'actives']);
    Route::get('/matieres/programme/{programme_id}', [MatiereController::class, 'byProgramme']);
    Route::apiResource('matieres', MatiereController::class);

    // Gestion des classes
    Route::get('/classes/actives', [ClasseController::class, 'actives']);
    Route::get('/classes/niveau/{niveauId}', [ClasseController::class, 'byNiveau']);
    Route::get('/classes/programme/{programmeId}', [ClasseController::class, 'byProgramme']);
    Route::apiResource('classes', ClasseController::class);

    // Routes spécifiques aux enseignants (doivent être avant apiResource)
       Route::prefix('personnel/enseignants')->group(function () {
        Route::get('/', [PersonnelController::class, 'enseignants']);
        Route::get('/{id}', [PersonnelController::class, 'enseignant']);
        Route::get('/statistiques', [PersonnelController::class, 'statistiquesEnseignants']);
        Route::post('/{id}/associer-matiere', [PersonnelController::class, 'associerMatiere']);
        Route::delete('/{id}/dissocier-matiere/{matiereId}', [PersonnelController::class, 'dissocierMatiere']);
        
        // Nouvelles routes pour les classes
        Route::post('/{id}/associer-classe', [PersonnelController::class, 'associerClasse']);
        Route::post('/{id}/dissocier-classe/{classeId}', [PersonnelController::class, 'dissocierClasse']);
        Route::get('/{id}/classes', [PersonnelController::class, 'classesEnseignant']);
    });

    // Gestion du personnel - Routes spécifiques AVANT la ressource
    Route::get('/personnel/actifs', [PersonnelController::class, 'actifs']);
    Route::get('/personnel/type/{type}', [PersonnelController::class, 'parType']);
    Route::post('/personnel/{id}/toggle-status', [PersonnelController::class, 'toggleStatus']);
    
    // Ressource principale du personnel
    Route::apiResource('personnel', PersonnelController::class);

    // Gestion des élèves
    
    Route::get('/eleves/actifs', [EleveController::class, 'actifs']);
    Route::get('/eleves/niveau/{niveauId}', [EleveController::class, 'parNiveau']);
    Route::get('/eleves/{id}/parents', [EleveController::class, 'parents']);
    Route::get('/eleves/{id}/inscriptions', [EleveController::class, 'inscriptions']);
    Route::post('/eleves/{id}/toggle-status', [EleveController::class, 'toggleStatus']);
    Route::get('/eleves/rechercher-parents', [EleveController::class, 'rechercherParents']);
    Route::post('/eleves/inscription-complete', [EleveController::class, 'inscriptionComplete']);
    Route::apiResource('eleves', EleveController::class);
    // Gestion des parents
    Route::get('/parents/actifs', [ParentController::class, 'actifs']);
    Route::get('/parents/{id}/eleves', [ParentController::class, 'eleves']);
    Route::post('/parents/{parentId}/associer-eleve', [ParentController::class, 'associerEleve']);
    Route::delete('/parents/{parentId}/dissocier-eleve', [ParentController::class, 'dissocierEleve']);
    Route::post('/parents/{id}/toggle-status', [ParentController::class, 'toggleStatus']);
    Route::apiResource('parents', ParentController::class);

    // ========================================
    // PHASE 3 : MODULE INSCRIPTIONS
    // ========================================

    // Inscriptions Académiques
    Route::get('/inscriptions-eleves/eleve/{eleveId}', [InscriptionEleveController::class, 'inscriptionsEleve']);
    Route::apiResource('inscriptions-eleves', InscriptionEleveController::class);

    // Inscriptions Financières
    Route::get('/inscriptions-financieres/eleve/{eleveId}', [InscriptionFinanciereController::class, 'inscriptionsEleve']);
    Route::get('/inscriptions-financieres/{id}/solde', [InscriptionFinanciereController::class, 'calculerSolde']);
    Route::apiResource('inscriptions-financieres', InscriptionFinanciereController::class);

    // ========================================
    // PHASE 4 : MODULE PÉDAGOGIQUE
    // ========================================

    // Gestion des enseignants via le PersonnelController
    // Les enseignants sont gérés comme un type de personnel
    // Utiliser les routes du personnel avec filtre par type

    // Gestion des emplois du temps
    Route::get('/emplois-du-temps/classe/{classeId}', [EmploiDuTempsController::class, 'emploiClasse']);
    Route::get('/emplois-du-temps/enseignant/{enseignantId}', [EmploiDuTempsController::class, 'emploiEnseignant']);
    Route::get('/emplois-du-temps/statistiques', [EmploiDuTempsController::class, 'statistiques']);
    Route::apiResource('emplois-du-temps', EmploiDuTempsController::class);

    // Paramètres généraux (à implémenter plus tard)
    // Route::get('/parametres', [ParametreController::class, 'index']);
    // Route::post('/parametres', [ParametreController::class, 'store']);
    // Route::put('/parametres/{key}', [ParametreController::class, 'update']);
}); 