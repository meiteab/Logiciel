<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\Matiere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnseignantMatiereController extends Controller
{
    /**
     * Afficher la liste des compétences enseignants.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('enseignants_matieres')
                ->join('personnels', 'enseignants_matieres.personnel_id', '=', 'personnels.id')
                ->join('matieres', 'enseignants_matieres.matiere_id', '=', 'matieres.id')
                ->select(
                    'enseignants_matieres.*',
                    'personnels.prenom as enseignant_prenom',
                    'personnels.nom_famille as enseignant_nom',
                    'personnels.matricule as enseignant_matricule',
                    'matieres.nom as matiere_nom',
                    'matieres.code as matiere_code'
                );

            // Filtrage par enseignant
            if ($request->has('personnel_id')) {
                $query->where('enseignants_matieres.personnel_id', $request->personnel_id);
            }

            // Filtrage par matière
            if ($request->has('matiere_id')) {
                $query->where('enseignants_matieres.matiere_id', $request->matiere_id);
            }

            // Filtrage par niveau de compétence
            if ($request->has('niveau_competence')) {
                $query->where('enseignants_matieres.niveau_competence', $request->niveau_competence);
            }

            // Filtrage par statut
            if ($request->has('est_actif')) {
                $query->where('enseignants_matieres.est_actif', $request->boolean('est_actif'));
            }

            // Recherche par nom d'enseignant ou matière
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('personnels.prenom', 'like', "%{$search}%")
                      ->orWhere('personnels.nom_famille', 'like', "%{$search}%")
                      ->orWhere('matieres.nom', 'like', "%{$search}%")
                      ->orWhere('matieres.code', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'personnels.nom_famille');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $result = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des compétences enseignants récupérée',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des compétences enseignants', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des compétences enseignants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle compétence enseignant.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'personnel_id' => 'required|exists:personnels,id',
                'matiere_id' => 'required|exists:matieres,id',
                'niveau_competence' => 'required|in:debutant,intermediaire,expert',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si la compétence existe déjà
            $existing = DB::table('enseignants_matieres')
                ->where('personnel_id', $request->personnel_id)
                ->where('matiere_id', $request->matiere_id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette compétence existe déjà pour cet enseignant'
                ], 400);
            }

            $competence = DB::table('enseignants_matieres')->insertGetId([
                'personnel_id' => $request->personnel_id,
                'matiere_id' => $request->matiere_id,
                'niveau_competence' => $request->niveau_competence,
                'est_actif' => $request->boolean('est_actif', true),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Récupérer les données complètes
            $result = DB::table('enseignants_matieres')
                ->join('personnels', 'enseignants_matieres.personnel_id', '=', 'personnels.id')
                ->join('matieres', 'enseignants_matieres.matiere_id', '=', 'matieres.id')
                ->where('enseignants_matieres.id', $competence)
                ->select(
                    'enseignants_matieres.*',
                    'personnels.prenom as enseignant_prenom',
                    'personnels.nom_famille as enseignant_nom',
                    'personnels.matricule as enseignant_matricule',
                    'matieres.nom as matiere_nom',
                    'matieres.code as matiere_code'
                )
                ->first();

            Log::info('Compétence enseignant créée', [
                'competence_id' => $competence,
                'personnel_id' => $request->personnel_id,
                'matiere_id' => $request->matiere_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compétence enseignant créée avec succès',
                'data' => $result
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la compétence enseignant', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la compétence enseignant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une compétence enseignant spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $competence = DB::table('enseignants_matieres')
                ->join('personnels', 'enseignants_matieres.personnel_id', '=', 'personnels.id')
                ->join('matieres', 'enseignants_matieres.matiere_id', '=', 'matieres.id')
                ->where('enseignants_matieres.id', $id)
                ->select(
                    'enseignants_matieres.*',
                    'personnels.prenom as enseignant_prenom',
                    'personnels.nom_famille as enseignant_nom',
                    'personnels.matricule as enseignant_matricule',
                    'matieres.nom as matiere_nom',
                    'matieres.code as matiere_code'
                )
                ->first();

            if (!$competence) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compétence enseignant non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Compétence enseignant récupérée',
                'data' => $competence
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la compétence enseignant', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la compétence enseignant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une compétence enseignant.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'niveau_competence' => 'sometimes|required|in:debutant,intermediaire,expert',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $competence = DB::table('enseignants_matieres')->find($id);

            if (!$competence) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compétence enseignant non trouvée'
                ], 404);
            }

            $updateData = [];
            if ($request->has('niveau_competence')) {
                $updateData['niveau_competence'] = $request->niveau_competence;
            }
            if ($request->has('est_actif')) {
                $updateData['est_actif'] = $request->boolean('est_actif');
            }
            $updateData['updated_at'] = now();

            DB::table('enseignants_matieres')
                ->where('id', $id)
                ->update($updateData);

            // Récupérer les données mises à jour
            $result = DB::table('enseignants_matieres')
                ->join('personnels', 'enseignants_matieres.personnel_id', '=', 'personnels.id')
                ->join('matieres', 'enseignants_matieres.matiere_id', '=', 'matieres.id')
                ->where('enseignants_matieres.id', $id)
                ->select(
                    'enseignants_matieres.*',
                    'personnels.prenom as enseignant_prenom',
                    'personnels.nom_famille as enseignant_nom',
                    'personnels.matricule as enseignant_matricule',
                    'matieres.nom as matiere_nom',
                    'matieres.code as matiere_code'
                )
                ->first();

            Log::info('Compétence enseignant mise à jour', [
                'competence_id' => $id,
                'updates' => $updateData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compétence enseignant mise à jour avec succès',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la compétence enseignant', [
                'error' => $e->getMessage(),
                'id' => $id,
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la compétence enseignant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une compétence enseignant.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $competence = DB::table('enseignants_matieres')->find($id);

            if (!$competence) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compétence enseignant non trouvée'
                ], 404);
            }

            // Vérifier si la compétence est utilisée dans des emplois du temps
            $usedInSchedule = DB::table('enseignants_classes')
                ->where('personnel_id', $competence->personnel_id)
                ->where('matiere_id', $competence->matiere_id)
                ->exists();

            if ($usedInSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette compétence car elle est utilisée dans des emplois du temps'
                ], 400);
            }

            DB::table('enseignants_matieres')->where('id', $id)->delete();

            Log::info('Compétence enseignant supprimée', [
                'competence_id' => $id,
                'personnel_id' => $competence->personnel_id,
                'matiere_id' => $competence->matiere_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compétence enseignant supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la compétence enseignant', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la compétence enseignant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les compétences d'un enseignant spécifique.
     */
    public function getCompetencesEnseignant(int $personnelId): JsonResponse
    {
        try {
            $competences = DB::table('enseignants_matieres')
                ->join('matieres', 'enseignants_matieres.matiere_id', '=', 'matieres.id')
                ->where('enseignants_matieres.personnel_id', $personnelId)
                ->where('enseignants_matieres.est_actif', true)
                ->select(
                    'enseignants_matieres.*',
                    'matieres.nom as matiere_nom',
                    'matieres.code as matiere_code'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Compétences de l\'enseignant récupérées',
                'data' => $competences
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des compétences de l\'enseignant', [
                'error' => $e->getMessage(),
                'personnel_id' => $personnelId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des compétences de l\'enseignant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les enseignants compétents pour une matière.
     */
    public function getEnseignantsMatiere(int $matiereId): JsonResponse
    {
        try {
            $enseignants = DB::table('enseignants_matieres')
                ->join('personnels', 'enseignants_matieres.personnel_id', '=', 'personnels.id')
                ->where('enseignants_matieres.matiere_id', $matiereId)
                ->where('enseignants_matieres.est_actif', true)
                ->where('personnels.statut', 'actif')
                ->select(
                    'enseignants_matieres.*',
                    'personnels.prenom',
                    'personnels.nom_famille',
                    'personnels.matricule'
                )
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Enseignants compétents pour la matière récupérés',
                'data' => $enseignants
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des enseignants compétents', [
                'error' => $e->getMessage(),
                'matiere_id' => $matiereId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enseignants compétents',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
