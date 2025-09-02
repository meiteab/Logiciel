<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfilController extends Controller
{
    /**
     * Afficher la liste des profils.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Profil::query();

            // Filtrage par statut
            if ($request->has('est_actif')) {
                $query->where('est_actif', $request->boolean('est_actif'));
            }

            // Recherche par nom ou code
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $profils = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des profils récupérée avec succès',
                'data' => $profils
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des profils',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un profil spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $profil = Profil::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Profil récupéré avec succès',
                'data' => $profil
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau profil.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:profils,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'permissions_specifiques' => 'nullable|array',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $profil = Profil::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profil créé avec succès',
                'data' => $profil
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un profil.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $profil = Profil::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:profils,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'permissions_specifiques' => 'nullable|array',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $profil->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => $profil
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un profil.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $profil = Profil::findOrFail($id);

            // Vérifier si le profil est utilisé par des utilisateurs
            if ($profil->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce profil car il est utilisé par des utilisateurs'
                ], 400);
            }

            $profil->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profil supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un profil.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $profil = Profil::findOrFail($id);
            $profil->est_actif = !$profil->est_actif;
            $profil->save();

            $status = $profil->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Profil {$status} avec succès",
                'data' => $profil
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les profils actifs.
     */
    public function actifs(): JsonResponse
    {
        try {
            $profils = Profil::where('est_actif', true)
                           ->orderBy('nom')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Profils actifs récupérés avec succès',
                'data' => $profils
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des profils actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
