<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgrammeController extends Controller
{
    /**
     * Afficher la liste des programmes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Programme::query();

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
            $programmes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des programmes récupérée avec succès',
                'data' => $programmes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des programmes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un programme spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $programme = Programme::with(['niveaux', 'matieres'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Programme récupéré avec succès',
                'data' => $programme
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Programme non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau programme.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:programmes,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:francais,arabe,autre',
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

            $programme = Programme::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Programme créé avec succès',
                'data' => $programme
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un programme.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $programme = Programme::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:programmes,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'sometimes|required|in:francais,arabe,autre',
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

            $programme->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Programme mis à jour avec succès',
                'data' => $programme
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un programme.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $programme = Programme::findOrFail($id);

            // Vérifier si le programme est utilisé dans d'autres tables
            if ($programme->classes()->count() > 0 || $programme->inscriptions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce programme car il est utilisé dans des classes ou des inscriptions'
                ], 400);
            }

            $programme->delete();

            return response()->json([
                'success' => true,
                'message' => 'Programme supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un programme.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $programme = Programme::findOrFail($id);
            $programme->est_actif = !$programme->est_actif;
            $programme->save();

            $status = $programme->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Programme {$status} avec succès",
                'data' => $programme
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
     * Récupérer les programmes actifs.
     */
    public function actifs(): JsonResponse
    {
        try {
            $programmes = Programme::where('est_actif', true)
                                 ->orderBy('nom')
                                 ->get();

            return response()->json([
                'success' => true,
                'message' => 'Programmes actifs récupérés avec succès',
                'data' => $programmes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des programmes actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les niveaux d'un programme.
     */
    public function niveaux(int $id): JsonResponse
    {
        try {
            $programme = Programme::with('niveaux')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Niveaux du programme récupérés avec succès',
                'data' => $programme->niveaux
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
     * Récupérer les matières d'un programme.
     */
    public function matieres(int $id): JsonResponse
    {
        try {
            $programme = Programme::with('matieres')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Matières du programme récupérées avec succès',
                'data' => $programme->matieres
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matières',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
