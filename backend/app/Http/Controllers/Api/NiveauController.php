<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NiveauController extends Controller
{
    /**
     * Afficher la liste des niveaux.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Niveau::query();

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
            $sortBy = $request->get('sort_by', 'ordre');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $niveaux = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des niveaux récupérée avec succès',
                'data' => $niveaux
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des niveaux',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un niveau spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $niveau = Niveau::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Niveau récupéré avec succès',
                'data' => $niveau
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Niveau non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau niveau.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:niveaux,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'ordre' => 'required|integer|min:1',
                'duree_annees' => 'nullable|integer|min:1',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $niveau = Niveau::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Niveau créé avec succès',
                'data' => $niveau
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un niveau.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $niveau = Niveau::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:niveaux,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'ordre' => 'sometimes|required|integer|min:1',
                'duree_annees' => 'nullable|integer|min:1',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $niveau->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Niveau mis à jour avec succès',
                'data' => $niveau
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un niveau.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $niveau = Niveau::findOrFail($id);

            // Vérifier si le niveau est utilisé dans d'autres tables
            if ($niveau->classes()->count() > 0 || $niveau->eleves()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce niveau car il est utilisé dans des classes ou par des élèves'
                ], 400);
            }

            $niveau->delete();

            return response()->json([
                'success' => true,
                'message' => 'Niveau supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un niveau.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $niveau = Niveau::findOrFail($id);
            $niveau->est_actif = !$niveau->est_actif;
            $niveau->save();

            $status = $niveau->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Niveau {$status} avec succès",
                'data' => $niveau
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
     * Récupérer les niveaux actifs.
     */
    public function actifs(): JsonResponse
    {
        try {
            $niveaux = Niveau::where('est_actif', true)
                           ->orderBy('ordre')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Niveaux actifs récupérés avec succès',
                'data' => $niveaux
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des niveaux actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les niveaux par programme.
     */
    public function byProgramme(int $programmeId): JsonResponse
    {
        try {
            $niveaux = Niveau::whereHas('programmes', function ($query) use ($programmeId) {
                $query->where('programme_id', $programmeId);
            })
            ->where('est_actif', true)
            ->orderBy('ordre')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Niveaux du programme récupérés avec succès',
                'data' => $niveaux
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des niveaux par programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
