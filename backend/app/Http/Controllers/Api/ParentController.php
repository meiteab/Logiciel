<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParentController extends Controller
{
    /**
     * Afficher la liste des parents.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ParentModel::query();

            // Filtrage par statut
            if ($request->has('est_actif')) {
                $query->where('est_actif', $request->boolean('est_actif'));
            }

            // Recherche par nom, prénom ou téléphone
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nom_famille', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom_famille');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $parents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des parents récupérée avec succès',
                'data' => $parents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des parents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un parent spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $parent = ParentModel::with(['eleves'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Parent récupéré avec succès',
                'data' => $parent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parent non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau parent.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom_famille' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'civilite' => 'required|in:homme,femme',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'profession' => 'nullable|string|max:255',
                'employeur' => 'nullable|string|max:255',
                'telephone_bureau' => 'nullable|string|max:20',
                'revenu_mensuel' => 'nullable|numeric|min:0',
                'niveau_etudes' => 'nullable|string|max:255',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parent = ParentModel::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Parent créé avec succès',
                'data' => $parent
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un parent.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $parent = ParentModel::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nom_famille' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'civilite' => 'sometimes|required|in:homme,femme',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'profession' => 'nullable|string|max:255',
                'employeur' => 'nullable|string|max:255',
                'telephone_bureau' => 'nullable|string|max:20',
                'revenu_mensuel' => 'nullable|numeric|min:0',
                'niveau_etudes' => 'nullable|string|max:255',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parent->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Parent mis à jour avec succès',
                'data' => $parent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un parent.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $parent = ParentModel::findOrFail($id);

            // Vérifier si le parent est lié à des élèves
            if ($parent->eleves()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce parent car il est lié à des élèves'
                ], 400);
            }

            $parent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parent supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un parent.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $parent = ParentModel::findOrFail($id);
            $parent->est_actif = !$parent->est_actif;
            $parent->save();

            $status = $parent->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Parent {$status} avec succès",
                'data' => $parent
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
     * Récupérer les parents actifs.
     */
    public function actifs(): JsonResponse
    {
        try {
            $parents = ParentModel::where('est_actif', true)
                           ->orderBy('nom_famille')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => 'Parents actifs récupérés avec succès',
                'data' => $parents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des parents actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les élèves d'un parent.
     */
    public function eleves(int $id): JsonResponse
    {
        try {
            $parent = ParentModel::with('eleves')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Élèves du parent récupérés avec succès',
                'data' => $parent->eleves
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des élèves',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Associer un parent à un élève.
     */
    public function associerEleve(Request $request, int $parentId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id',
                'role' => 'required|in:pere,mere,tuteur,autre',
                'est_responsable_legal' => 'boolean',
                'ordre_priorite' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $parent = ParentModel::findOrFail($parentId);
            $eleveId = $request->get('eleve_id');

            // Vérifier si l'association existe déjà
            $associationExists = \DB::table('eleves_parents')
                ->where('eleve_id', $eleveId)
                ->where('parent_id', $parentId)
                ->where('role', $request->get('role'))
                ->exists();

            if ($associationExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette association parent-élève existe déjà'
                ], 400);
            }

            // Créer l'association
            \DB::table('eleves_parents')->insert([
                'eleve_id' => $eleveId,
                'parent_id' => $parentId,
                'role' => $request->get('role'),
                'est_responsable_legal' => $request->get('est_responsable_legal', false),
                'ordre_priorite' => $request->get('ordre_priorite', 1),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parent associé à l\'élève avec succès'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'association',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dissocier un parent d'un élève.
     */
    public function dissocierEleve(Request $request, int $parentId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id',
                'role' => 'required|in:pere,mere,tuteur,autre'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deleted = \DB::table('eleves_parents')
                ->where('eleve_id', $request->get('eleve_id'))
                ->where('parent_id', $parentId)
                ->where('role', $request->get('role'))
                ->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Association parent-élève non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Parent dissocié de l\'élève avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la dissociation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
