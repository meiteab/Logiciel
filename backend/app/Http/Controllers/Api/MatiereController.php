<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatiereController extends Controller
{
    /**
     * Afficher la liste des matières.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Matiere::query();

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
            $matieres = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des matières récupérée avec succès',
                'data' => $matieres
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une matière spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $matiere = Matiere::with(['niveaux', 'programmes'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Matière récupérée avec succès',
                'data' => $matiere
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Matière non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle matière.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:matieres,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'couleur' => 'nullable|string|max:7',
                'icone' => 'nullable|string|max:255',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $matiere = Matiere::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Matière créée avec succès',
                'data' => $matiere
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une matière.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $matiere = Matiere::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:matieres,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'couleur' => 'nullable|string|max:7',
                'icone' => 'nullable|string|max:255',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $matiere->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Matière mise à jour avec succès',
                'data' => $matiere
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une matière.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $matiere = Matiere::findOrFail($id);

            // Vérifier si la matière est utilisée dans d'autres tables
            if ($matiere->classes()->count() > 0 || $matiere->evaluations()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette matière car elle est utilisée dans des classes ou des évaluations'
                ], 400);
            }

            $matiere->delete();

            return response()->json([
                'success' => true,
                'message' => 'Matière supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une matière.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $matiere = Matiere::findOrFail($id);
            $matiere->est_actif = !$matiere->est_actif;
            $matiere->save();

            $status = $matiere->est_actif ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Matière {$status} avec succès",
                'data' => $matiere
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
     * Récupérer les matières actives.
     */
    public function actives(): JsonResponse
    {
        try {
            $matieres = Matiere::where('est_actif', true)
                              ->orderBy('nom')
                              ->get();

            return response()->json([
                'success' => true,
                'message' => 'Matières actives récupérées avec succès',
                'data' => $matieres
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matières actives',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les matières par programme.
     */
    public function byProgramme(int $programmeId): JsonResponse
    {
        try {
            $matieres = Matiere::whereHas('programmes', function ($query) use ($programmeId) {
                $query->where('programme_id', $programmeId);
            })
            ->where('est_actif', true)
            ->orderBy('nom')
            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Matières du programme récupérées avec succès',
                'data' => $matieres
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matières par programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
