<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnneeScolaireController extends Controller
{
    /**
     * Afficher la liste des années scolaires.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = AnneeScolaire::query();

            // Filtrage par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->get('statut'));
            }

            // Filtrage par année courante
            if ($request->has('est_annee_courante')) {
                $query->where('est_annee_courante', $request->boolean('est_annee_courante'));
            }

            // Recherche par code ou nom
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('nom', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'date_debut');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $annees = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des années scolaires récupérée avec succès',
                'data' => $annees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des années scolaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une année scolaire spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $annee = AnneeScolaire::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Année scolaire récupérée avec succès',
                'data' => $annee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Année scolaire non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle année scolaire.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:annees_scolaires,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after:date_debut',
                'statut' => 'required|in:planifiee,en_cours,terminee,archivee',
                'est_annee_courante' => 'boolean',
                'nombre_trimestres' => 'required|integer|min:1|max:4',
                'nombre_semestres' => 'required|integer|min:1|max:2',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Si c'est l'année courante, désactiver les autres
            if ($request->boolean('est_annee_courante')) {
                AnneeScolaire::where('est_annee_courante', true)
                    ->update(['est_annee_courante' => false]);
            }

            $annee = AnneeScolaire::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Année scolaire créée avec succès',
                'data' => $annee
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'année scolaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une année scolaire.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $annee = AnneeScolaire::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|required|string|max:50|unique:annees_scolaires,code,' . $id,
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'sometimes|required|date',
                'date_fin' => 'sometimes|required|date|after:date_debut',
                'statut' => 'sometimes|required|in:planifiee,en_cours,terminee,archivee',
                'est_annee_courante' => 'boolean',
                'nombre_trimestres' => 'sometimes|required|integer|min:1|max:4',
                'nombre_semestres' => 'sometimes|required|integer|min:1|max:2',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Si c'est l'année courante, désactiver les autres
            if ($request->boolean('est_annee_courante')) {
                AnneeScolaire::where('est_annee_courante', true)
                    ->where('id', '!=', $id)
                    ->update(['est_annee_courante' => false]);
            }

            $annee->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Année scolaire mise à jour avec succès',
                'data' => $annee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'année scolaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une année scolaire.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $annee = AnneeScolaire::findOrFail($id);

            // Vérifier si l'année est utilisée
            if ($annee->est_annee_courante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer l\'année scolaire courante'
                ], 400);
            }

            $annee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Année scolaire supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'année scolaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'année scolaire courante.
     */
    public function current(): JsonResponse
    {
        try {
            $annee = AnneeScolaire::where('est_annee_courante', true)->first();

            if (!$annee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune année scolaire courante définie'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Année scolaire courante récupérée',
                'data' => $annee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'année courante',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
