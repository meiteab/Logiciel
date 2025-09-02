<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClasseController extends Controller
{
    /**
     * Afficher la liste des classes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Classe::with(['niveaux.programme', 'programmes', 'anneesScolaires']);

            // Filtrage par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->get('statut'));
            }

            // Filtrage par niveau
            if ($request->has('niveau_id')) {
                $query->whereHas('niveaux', function ($q) use ($request) {
                    $q->where('niveau_id', $request->get('niveau_id'));
                });
            }

            // Filtrage par programme
            if ($request->has('programme_id')) {
                $query->whereHas('niveau', function ($q) use ($request) {
                    $q->where('programme_id', $request->get('programme_id'));
                });
            }

            // Filtrage par année scolaire
            if ($request->has('annee_scolaire_id')) {
                $query->whereHas('anneesScolaires', function ($q) use ($request) {
                    $q->where('annee_scolaire_id', $request->get('annee_scolaire_id'));
                });
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
            $classes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des classes récupérée avec succès',
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une classe spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $classe = Classe::with(['niveaux.programme', 'programmes', 'anneesScolaires', 'eleves'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Classe récupérée avec succès',
                'data' => $classe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Classe non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle classe.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:classes,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'niveau_id' => 'required|exists:niveaux,id',
                'programme_id' => 'required|exists:programmes,id',
                'annee_scolaire_id' => 'required|exists:annees_scolaires,id',
                'capacite_max' => 'nullable|integer|min:1',
                'salle_id' => 'nullable|exists:salles,id',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classe = Classe::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Classe créée avec succès',
                'data' => $classe
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une classe.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $classe = Classe::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:classes,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'niveau_id' => 'sometimes|required|exists:niveaux,id',
                'programme_id' => 'sometimes|required|exists:programmes,id',
                'annee_scolaire_id' => 'sometimes|required|exists:annees_scolaires,id',
                'capacite_max' => 'nullable|integer|min:1',
                'salle_id' => 'nullable|exists:salles,id',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $classe->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Classe mise à jour avec succès',
                'data' => $classe
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une classe.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $classe = Classe::findOrFail($id);

            // Vérifier si la classe est utilisée dans d'autres tables
            if ($classe->eleves()->count() > 0 || $classe->inscriptions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette classe car elle contient des élèves ou des inscriptions'
                ], 400);
            }

            $classe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Classe supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la classe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une classe.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $classe = Classe::findOrFail($id);
            $classe->est_actif = !$classe->est_actif;
            $classe->save();

            $status = $classe->est_actif ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Classe {$status} avec succès",
                'data' => $classe
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
     * Récupérer les classes actives.
     */
    public function actives(): JsonResponse
    {
        try {
            $classes = Classe::with(['niveau', 'programme', 'anneeScolaire'])
                           ->where('est_actif', true)
                           ->orderBy('nom')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Classes actives récupérées avec succès',
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes actives',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les classes par niveau.
     */
    public function byNiveau(int $niveauId): JsonResponse
    {
        try {
            $classes = Classe::with(['niveau', 'programme', 'anneeScolaire'])
                           ->where('niveau_id', $niveauId)
                           ->where('est_actif', true)
                           ->orderBy('nom')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Classes du niveau récupérées avec succès',
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes par niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les classes par programme.
     */
    public function byProgramme(int $programmeId): JsonResponse
    {
        try {
            $classes = Classe::with(['niveau', 'programme', 'anneeScolaire'])
                           ->where('programme_id', $programmeId)
                           ->where('est_actif', true)
                           ->orderBy('nom')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Classes du programme récupérées avec succès',
                'data' => $classes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des classes par programme',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les élèves d'une classe.
     */
    public function eleves(int $id): JsonResponse
    {
        try {
            $classe = Classe::with('eleves')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Élèves de la classe récupérés avec succès',
                'data' => $classe->eleves
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des élèves',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
