<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InscriptionFinanciere;
use App\Models\Eleve;
use App\Models\AnneeScolaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InscriptionFinanciereController extends Controller
{
    /**
     * Afficher la liste des inscriptions financières.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = InscriptionFinanciere::with(['eleve', 'anneeScolaire']);

            // Filtrage par année scolaire
            if ($request->has('annee_scolaire_id')) {
                $query->where('annee_scolaire_id', $request->annee_scolaire_id);
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
                'message' => 'Liste des inscriptions financières récupérée avec succès',
                'data' => $inscriptions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des inscriptions financières', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions financières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une inscription financière spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inscription = InscriptionFinanciere::with(['eleve', 'anneeScolaire'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Inscription financière récupérée avec succès',
                'data' => $inscription
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inscription financière non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle inscription financière.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'eleve_id' => 'required|exists:eleves,id',
                'annee_scolaire_id' => 'required|exists:annees_scolaires,id',
                'montant_total' => 'required|numeric|min:0',
                'montant_paye' => 'required|numeric|min:0',
                'date_inscription' => 'required|date',
                'statut' => 'required|in:en_attente,validee,refusee,annulee',
                'mode_paiement' => 'nullable|string',
                'echeances' => 'nullable|json',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si l'élève n'a pas déjà une inscription financière pour cette année
            $inscriptionExistante = InscriptionFinanciere::where([
                'eleve_id' => $request->eleve_id,
                'annee_scolaire_id' => $request->annee_scolaire_id
            ])->first();

            if ($inscriptionExistante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet élève a déjà une inscription financière pour cette année scolaire'
                ], 400);
            }

            // Vérifier que le montant payé ne dépasse pas le montant total
            if ($request->montant_paye > $request->montant_total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant payé ne peut pas dépasser le montant total'
                ], 400);
            }

            $inscription = InscriptionFinanciere::create($request->all());

            $inscription->load(['eleve', 'anneeScolaire']);

            Log::info('Inscription financière créée', [
                'inscription_id' => $inscription->id,
                'eleve_id' => $inscription->eleve_id,
                'annee_scolaire_id' => $inscription->annee_scolaire_id,
                'montant_total' => $inscription->montant_total
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscription financière créée avec succès',
                'data' => $inscription
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'inscription financière', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'inscription financière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les inscriptions financières d'un élève spécifique.
     */
    public function inscriptionsEleve(int $eleveId): JsonResponse
    {
        try {
            $inscriptions = InscriptionFinanciere::with(['anneeScolaire'])
                ->where('eleve_id', $eleveId)
                ->orderBy('annee_scolaire_id', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Inscriptions financières de l\'élève récupérées avec succès',
                'data' => $inscriptions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des inscriptions financières de l\'élève', [
                'error' => $e->getMessage(),
                'eleve_id' => $eleveId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions financières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le solde restant d'une inscription financière.
     */
    public function calculerSolde(int $id): JsonResponse
    {
        try {
            $inscription = InscriptionFinanciere::findOrFail($id);
            
            $solde = $inscription->montant_total - $inscription->montant_paye;
            $pourcentagePaye = ($inscription->montant_total > 0) ? 
                round(($inscription->montant_paye / $inscription->montant_total) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Solde calculé avec succès',
                'data' => [
                    'inscription_id' => $inscription->id,
                    'montant_total' => $inscription->montant_total,
                    'montant_paye' => $inscription->montant_paye,
                    'solde_restant' => $solde,
                    'pourcentage_paye' => $pourcentagePaye,
                    'statut_paiement' => $solde <= 0 ? 'paye' : ($inscription->montant_paye > 0 ? 'partiel' : 'impaye')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul du solde', [
                'error' => $e->getMessage(),
                'inscription_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du solde',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
