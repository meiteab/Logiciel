<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InscriptionEleve;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\AnneeScolaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscriptionEleveController extends Controller
{
    /**
     * Afficher la liste des inscriptions d'élèves.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = InscriptionEleve::with(['eleve', 'classeFrancaise', 'classeArabe', 'anneeScolaire']);

            // Filtrage par année scolaire
            if ($request->has('annee_scolaire_id')) {
                $query->where('annee_scolaire_id', $request->annee_scolaire_id);
            }

            // Filtrage par classe française
            if ($request->has('classe_francaise_id')) {
                $query->where('classe_francaise_id', $request->classe_francaise_id);
            }

            // Filtrage par classe arabe
            if ($request->has('classe_arabe_id')) {
                $query->where('classe_arabe_id', $request->classe_arabe_id);
            }

            // Filtrage par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            // Recherche par nom d'élève
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('eleve', function ($q) use ($search) {
                    $q->where('prenom', 'like', "%{$search}%")
                      ->orWhere('nom_famille', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $inscriptions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des inscriptions récupérée avec succès',
                'data' => $inscriptions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des inscriptions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une inscription spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inscription = InscriptionEleve::with(['eleve', 'classeFrancaise', 'classeArabe', 'anneeScolaire'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Inscription récupérée avec succès',
                'data' => $inscription
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inscription non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle inscription d'élève.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id',
                'classe_francaise_id' => 'required|exists:classes,id',
                'classe_arabe_id' => 'required|exists:classes,id',
                'annee_scolaire_id' => 'required|exists:annees_scolaires,id',
                'date_inscription' => 'required|date',
                'date_sortie' => 'nullable|date',
                'type_inscription' => 'required|in:nouvelle,reinscription,transfert',
                'statut' => 'required|in:inscrit,redoublement,transfert,sortie,suspendu',
                'motif_sortie' => 'nullable|string',
                'notes_administratives' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si l'élève n'est pas déjà inscrit pour cette année
            $inscriptionExistante = InscriptionEleve::where([
                'eleve_id' => $request->eleve_id,
                'annee_scolaire_id' => $request->annee_scolaire_id
            ])->first();

            if ($inscriptionExistante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet élève est déjà inscrit pour cette année scolaire'
                ], 400);
            }

            $inscription = InscriptionEleve::create($request->all());

            $inscription->load(['eleve', 'classeFrancaise', 'classeArabe', 'anneeScolaire']);

            Log::info('Inscription élève créée', [
                'inscription_id' => $inscription->id,
                'eleve_id' => $inscription->eleve_id,
                'classe_francaise_id' => $inscription->classe_francaise_id,
                'classe_arabe_id' => $inscription->classe_arabe_id,
                'annee_scolaire_id' => $inscription->annee_scolaire_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscription créée avec succès',
                'data' => $inscription
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'inscription', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les inscriptions d'un élève spécifique.
     */
    public function inscriptionsEleve(int $eleveId): JsonResponse
    {
        try {
            $inscriptions = InscriptionEleve::with(['classe', 'anneeScolaire'])
                ->where('eleve_id', $eleveId)
                ->orderBy('annee_scolaire_id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Inscriptions de l\'élève récupérées avec succès',
                'data' => $inscriptions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des inscriptions de l\'élève', [
                'error' => $e->getMessage(),
                'eleve_id' => $eleveId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
