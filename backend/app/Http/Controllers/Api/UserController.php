<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profil;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Models\Personnel;
use App\Services\UserAccountService;
use App\Services\IdentifiantGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['profil', 'personnel']);

        // Filtrage
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('profil_id')) {
            $query->where('profil_id', $request->profil_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('personnel', function($q2) use ($search) {
                      $q2->where('prenom', 'like', "%{$search}%")
                         ->orWhere('nom_famille', 'like', "%{$search}%")
                         ->orWhere('matricule', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Enregistrer un nouvel utilisateur.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'profil_id' => 'required|exists:profils,id',
            'statut' => 'required|in:actif,inactif,suspendu',
            'email_verified_at' => 'nullable|date',
            'two_factor_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profil_id' => $request->profil_id,
            'statut' => $request->statut,
            'email_verified_at' => $request->email_verified_at,
            'two_factor_enabled' => $request->two_factor_enabled ?? false,
        ]);

        $user->load(['profil', 'personnel']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'data' => $user
        ], 201);
    }

    /**
     * Afficher l'utilisateur spécifié.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['profil', 'personnel']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Mettre à jour l'utilisateur spécifié.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'profil_id' => 'required|exists:profils,id',
            'statut' => 'required|in:actif,inactif,suspendu',
            'email_verified_at' => 'nullable|date',
            'two_factor_enabled' => 'boolean',
            'locked_until' => 'nullable|date',
            'failed_login_attempts' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only([
            'email',
            'profil_id',
            'statut',
            'email_verified_at',
            'two_factor_enabled',
            'locked_until',
            'failed_login_attempts',
        ]));

        $user->load(['profil', 'personnel']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
            'data' => $user
        ]);
    }

    /**
     * Supprimer l'utilisateur spécifié.
     */
    public function destroy(User $user): JsonResponse
    {
        // Vérifier si l'utilisateur a un personnel associé
        if ($user->personnel) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer un utilisateur avec un enregistrement de personnel associé'
            ], 400);
        }

        // Révoquer tous les tokens
        $user->tokens()->delete();

        // Suppression douce de l'utilisateur
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les profils disponibles pour la création/modification d'utilisateur.
     */
    public function profiles(): JsonResponse
    {
        $profiles = Profil::where('est_actif', true)
                         ->orderBy('nom')
                         ->get(['id', 'nom', 'code', 'description']);

        return response()->json([
            'success' => true,
            'data' => $profiles
        ]);
    }

    /**
     * Verrouiller/déverrouiller le compte utilisateur.
     */
    public function toggleLock(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'locked_until' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'locked_until' => $request->locked_until,
            'failed_login_attempts' => $request->locked_until ? 5 : 0
        ]);

        $action = $request->locked_until ? 'verrouillé' : 'déverrouillé';

        return response()->json([
            'success' => true,
            'message' => "Compte utilisateur {$action} avec succès",
            'data' => [
                'locked_until' => $user->locked_until,
                'is_locked' => $user->isLocked()
            ]
        ]);
    }

    /**
     * Réinitialiser le mot de passe de l'utilisateur.
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);

        // Révoquer tous les tokens pour forcer la reconnexion
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }

    // ========================================
    // NOUVELLES MÉTHODES POUR GESTION DES COMPTES
    // ========================================

    /**
     * Crée un compte utilisateur pour un élève
     */
    public function createEleveAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $eleve = Eleve::findOrFail($request->eleve_id);

            // Vérifier si l'élève a déjà un compte
            if ($eleve->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet élève a déjà un compte utilisateur'
                ], 400);
            }

            // Créer le compte utilisateur
            $user = UserAccountService::createEleveAccount($eleve);

            Log::info('Compte utilisateur créé pour élève', [
                'eleve_id' => $eleve->id,
                'user_id' => $user->id,
                'identifiant' => $user->identifiant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte utilisateur créé avec succès pour l\'élève',
                'data' => [
                    'user_id' => $user->id,
                    'identifiant' => $user->identifiant,
                    'eleve' => [
                        'id' => $eleve->id,
                        'nom_complet' => $eleve->prenom . ' ' . $eleve->nom_famille,
                        'matricule' => $eleve->matricule
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du compte élève', [
                'error' => $e->getMessage(),
                'eleve_id' => $request->eleve_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crée un compte utilisateur pour un parent
     */
    public function createParentAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'parent_id' => 'required|exists:parents,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parent = ParentModel::findOrFail($request->parent_id);

            // Vérifier si le parent a déjà un compte
            if ($parent->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce parent a déjà un compte utilisateur'
                ], 400);
            }

            // Créer le compte utilisateur
            $user = UserAccountService::createParentAccount($parent);

            Log::info('Compte utilisateur créé pour parent', [
                'parent_id' => $parent->id,
                'user_id' => $user->id,
                'identifiant' => $user->identifiant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte utilisateur créé avec succès pour le parent',
                'data' => [
                    'user_id' => $user->id,
                    'identifiant' => $user->identifiant,
                    'parent' => [
                        'id' => $parent->id,
                        'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du compte parent', [
                'error' => $e->getMessage(),
                'parent_id' => $request->parent_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crée un compte utilisateur pour un personnel
     */
    public function createPersonnelAccount(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'personnel_id' => 'required|exists:personnels,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $personnel = Personnel::findOrFail($request->personnel_id);

            // Vérifier si le personnel a déjà un compte
            if ($personnel->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce personnel a déjà un compte utilisateur'
                ], 400);
            }

            // Créer le compte utilisateur
            $user = UserAccountService::createPersonnelAccount($personnel);

            Log::info('Compte utilisateur créé pour personnel', [
                'personnel_id' => $personnel->id,
                'user_id' => $user->id,
                'identifiant' => $user->identifiant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compte utilisateur créé avec succès pour le personnel',
                'data' => [
                    'user_id' => $user->id,
                    'identifiant' => $user->identifiant,
                    'personnel' => [
                        'id' => $personnel->id,
                        'nom_complet' => $personnel->prenom . ' ' . $personnel->nom_famille,
                        'matricule' => $personnel->matricule,
                        'poste' => $personnel->poste
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du compte personnel', [
                'error' => $e->getMessage(),
                'personnel_id' => $request->personnel_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur avec génération automatique
     */
    public function resetPasswordAuto(User $user): JsonResponse
    {
        try {
            $newPassword = UserAccountService::resetPassword($user);

            Log::info('Mot de passe réinitialisé automatiquement', [
                'user_id' => $user->id,
                'identifiant' => $user->identifiant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'data' => [
                    'user_id' => $user->id,
                    'identifiant' => $user->identifiant,
                    'nouveau_mot_de_passe' => $newPassword
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation automatique du mot de passe', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Active ou désactive un compte utilisateur
     */
    public function toggleAccountStatus(Request $request, User $user): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:activate,deactivate'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->action === 'activate') {
                UserAccountService::activateAccount($user);
                $message = 'Compte utilisateur activé avec succès';
            } else {
                UserAccountService::deactivateAccount($user);
                $message = 'Compte utilisateur désactivé avec succès';
            }

            Log::info('Statut du compte utilisateur modifié', [
                'user_id' => $user->id,
                'identifiant' => $user->identifiant,
                'action' => $request->action,
                'nouveau_statut' => $user->statut
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user_id' => $user->id,
                    'identifiant' => $user->identifiant,
                    'statut' => $user->statut
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification du statut du compte', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'action' => $request->action ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste les utilisateurs sans compte
     */
    public function getUsersWithoutAccount(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'all'); // all, eleves, parents, personnel

            $data = [];

            if ($type === 'all' || $type === 'eleves') {
                $eleves = Eleve::whereNull('user_id')
                    ->select('id', 'prenom', 'nom_famille', 'matricule')
                    ->get()
                    ->map(function ($eleve) {
                        return [
                            'type' => 'eleve',
                            'id' => $eleve->id,
                            'nom_complet' => $eleve->prenom . ' ' . $eleve->nom_famille,
                            'identifiant' => $eleve->matricule
                        ];
                    });
                $data['eleves'] = $eleves;
            }

            if ($type === 'all' || $type === 'parents') {
                $parents = ParentModel::whereNull('user_id')
                    ->select('id', 'prenom', 'nom_famille')
                    ->get()
                    ->map(function ($parent) {
                        return [
                            'type' => 'parent',
                            'id' => $parent->id,
                            'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille
                        ];
                    });
                $data['parents'] = $parents;
            }

            if ($type === 'all' || $type === 'personnel') {
                $personnel = Personnel::whereNull('user_id')
                    ->select('id', 'prenom', 'nom_famille', 'matricule', 'poste')
                    ->get()
                    ->map(function ($personnel) {
                        return [
                            'type' => 'personnel',
                            'id' => $personnel->id,
                            'nom_complet' => $personnel->prenom . ' ' . $personnel->nom_famille,
                            'identifiant' => $personnel->matricule,
                            'poste' => $personnel->poste
                        ];
                    });
                $data['personnel'] = $personnel;
            }

            return response()->json([
                'success' => true,
                'message' => 'Liste des utilisateurs sans compte récupérée',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs sans compte', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs sans compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
