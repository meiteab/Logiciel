<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersonnelController extends Controller
{
    /**
     * Afficher la liste du personnel.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Personnel::query();

            // Filtrage par statut
            if ($request->has('est_actif')) {
                $query->where('est_actif', $request->boolean('est_actif'));
            }

            // Filtrage par type de personnel
            if ($request->has('type_personnel')) {
                $query->where('type_personnel', $request->get('type_personnel'));
            }

            // Recherche par nom, prénom ou matricule
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nom_famille', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%")
                      ->orWhere('email_professionnel', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom_famille');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $personnel = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste du personnel récupérée avec succès',
                'data' => $personnel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un membre du personnel spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $personnel = Personnel::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Membre du personnel récupéré avec succès',
                'data' => $personnel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Membre du personnel non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouveau membre du personnel.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'matricule' => 'required|string|max:50|unique:personnels,matricule',
                'nom_famille' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'date_naissance' => 'required|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'sexe' => 'required|in:homme,femme',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'email_professionnel' => 'nullable|email|unique:personnels,email_professionnel',
                'email_personnel' => 'nullable|email',
                'date_embauche' => 'required|date',
                'type_personnel' => 'required|in:enseignant,administratif,technique,direction,autre',
                'fonction' => 'required|string|max:255',
                'departement' => 'nullable|string|max:255',
                'diplome' => 'nullable|string',
                'specialite' => 'nullable|string|max:255',
                'numero_securite_sociale' => 'nullable|string|max:50',
                'numero_cnss' => 'nullable|string|max:50',
                'salaire_brut' => 'nullable|numeric|min:0',
                'salaire_net' => 'nullable|numeric|min:0',
                'banque' => 'nullable|string|max:255',
                'numero_compte' => 'nullable|string|max:50',
                'rib' => 'nullable|string|max:50',
                'est_actif' => 'boolean',
                'date_depart' => 'nullable|date|after:date_embauche',
                'motif_depart' => 'nullable|string',
                'photo' => 'nullable|string',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $personnel = Personnel::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Membre du personnel créé avec succès',
                'data' => $personnel
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du membre du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un membre du personnel.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $personnel = Personnel::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'matricule' => 'sometimes|required|string|max:50|unique:personnels,matricule,' . $id,
                'nom_famille' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'date_naissance' => 'sometimes|required|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'sexe' => 'sometimes|required|in:homme,femme',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'email_professionnel' => 'nullable|email|unique:personnels,email_professionnel,' . $id,
                'email_personnel' => 'nullable|email',
                'date_embauche' => 'sometimes|required|date',
                'type_personnel' => 'sometimes|required|in:enseignant,administratif,technique,direction,autre',
                'fonction' => 'sometimes|required|string|max:255',
                'departement' => 'nullable|string|max:255',
                'diplome' => 'nullable|string',
                'specialite' => 'nullable|string|max:255',
                'numero_securite_sociale' => 'nullable|string|max:50',
                'numero_cnss' => 'nullable|string|max:50',
                'salaire_brut' => 'nullable|numeric|min:0',
                'salaire_net' => 'nullable|numeric|min:0',
                'banque' => 'nullable|string|max:255',
                'numero_compte' => 'nullable|string|max:50',
                'rib' => 'nullable|string|max:50',
                'est_actif' => 'boolean',
                'date_depart' => 'nullable|date|after:date_embauche',
                'motif_depart' => 'nullable|string',
                'photo' => 'nullable|string',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $personnel->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Membre du personnel mis à jour avec succès',
                'data' => $personnel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du membre du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un membre du personnel.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $personnel = Personnel::findOrFail($id);

            // Vérifier si le personnel est lié à des utilisateurs
            if ($personnel->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce membre du personnel car il est lié à des comptes utilisateurs'
                ], 400);
            }

            $personnel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Membre du personnel supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du membre du personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un membre du personnel.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $personnel = Personnel::findOrFail($id);
            $personnel->est_actif = !$personnel->est_actif;
            $personnel->save();

            $status = $personnel->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Membre du personnel {$status} avec succès",
                'data' => $personnel
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
     * Récupérer le personnel actif.
     */
    public function actifs(): JsonResponse
    {
        try {
            $personnel = Personnel::where('est_actif', true)
                                ->orderBy('nom_famille')
                                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Personnel actif récupéré avec succès',
                'data' => $personnel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du personnel actif',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le personnel par type.
     */
    public function parType(string $type): JsonResponse
    {
        try {
            $personnel = Personnel::where('type_personnel', $type)
                                ->where('est_actif', true)
                                ->orderBy('nom_famille')
                                ->get();

            return response()->json([
                'success' => true,
                'message' => "Personnel de type '{$type}' récupéré avec succès",
                'data' => $personnel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du personnel par type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // MÉTHODES SPÉCIFIQUES AUX ENSEIGNANTS
    // ========================================

    /**
     * Récupérer tous les enseignants avec leurs matières et classes
     */
    public function enseignants(Request $request): JsonResponse
    {
        try {
            $query = Personnel::enseignants()
                ->with(['matieres', 'classes.matiere', 'classes.niveau.programme']);

            // Filtres spécifiques aux enseignants
            if ($request->filled('matiere_id')) {
                $query->whereHas('matieres', function ($q) use ($request) {
                    $q->where('matiere_id', $request->matiere_id);
                });
            }

            if ($request->filled('classe_id')) {
                $query->whereHas('classes', function ($q) use ($request) {
                    $q->where('classe_id', $request->classe_id);
                });
            }

            if ($request->filled('specialite')) {
                $query->parSpecialite($request->specialite);
            }

            // Recherche par nom/prénom
            if ($request->filled('recherche')) {
                $recherche = $request->recherche;
                $query->where(function ($q) use ($recherche) {
                    $q->where('nom_famille', 'like', "%{$recherche}%")
                      ->orWhere('prenom', 'like', "%{$recherche}%")
                      ->orWhere('matricule', 'like', "%{$recherche}%");
                });
            }

            // Tri et pagination
            $sortBy = $request->get('sort_by', 'nom_famille');
            $sortOrder = $request->get('sort_order', 'asc');
            $perPage = $request->get('per_page', 15);

            $enseignants = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des enseignants récupérée avec succès',
                'data' => $enseignants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des enseignants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un enseignant spécifique avec tous ses détails
     */
    public function enseignant(int $id): JsonResponse
    {
        try {
            $enseignant = Personnel::enseignants()
                ->with([
                    'user',
                    'matieres',
                    'classes.matiere',
                    'classes.niveau.programme',
                    'classes.anneeScolaire',
                    'absences',
                    'remplacementsEffectues',
                    'coursEmploiDuTemps'
                ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Enseignant récupéré avec succès',
                'data' => $enseignant
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enseignant non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Associer une matière à un enseignant
     */
    public function associerMatiere(Request $request, int $id): JsonResponse
    {
        try {
            $enseignant = Personnel::enseignants()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'matiere_id' => 'required|exists:matieres,id',
                'niveau_competence' => 'nullable|in:debutant,intermediaire,expert',
                'date_obtention_competence' => 'nullable|date',
                'commentaires_competence' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier si l'association existe déjà
            if ($enseignant->matieres()->where('matiere_id', $request->matiere_id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette matière est déjà associée à cet enseignant'
                ], 400);
            }

            $enseignant->matieres()->attach($request->matiere_id, [
                'niveau_competence' => $request->niveau_competence ?? 'intermediaire',
                'date_obtention_competence' => $request->date_obtention_competence,
                'commentaires_competence' => $request->commentaires_competence,
                'est_actif' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Matière associée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'association de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dissocier une matière d'un enseignant
     */
    public function dissocierMatiere(int $id, int $matiereId): JsonResponse
    {
        try {
            $enseignant = Personnel::enseignants()->findOrFail($id);
            $enseignant->matieres()->detach($matiereId);

            return response()->json([
                'success' => true,
                'message' => 'Matière dissociée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la dissociation de la matière',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des enseignants
     */
    public function statistiquesEnseignants(): JsonResponse
    {
        try {
            $stats = [
                'total' => Personnel::enseignants()->count(),
                'actifs' => Personnel::enseignants()->actifs()->count(),
                'inactifs' => Personnel::enseignants()->inactifs()->count(),
                'par_specialite' => Personnel::enseignants()
                    ->select('specialite', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->whereNotNull('specialite')
                    ->groupBy('specialite')
                    ->get(),
                'par_departement' => Personnel::enseignants()
                    ->select('departement', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->whereNotNull('departement')
                    ->groupBy('departement')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des enseignants récupérées avec succès',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Associer une classe à un enseignant
     */
    public function associerClasse(Request $request, int $id)
    {
        $request->validate([
            'classe_id' => 'required|integer|exists:classes,id',
            'matiere_id' => 'required|integer|exists:matieres,id',
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id',
            'role' => 'required|in:titulaire,suppleant,remplacant',
            'heures_semaine' => 'required|integer|min:1|max:40',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'statut' => 'required|in:actif,inactif,suspendu',
            'notes_pedagogiques' => 'nullable|string|max:1000'
        ]);

        $personnel = Personnel::findOrFail($id);
        
        if (!$personnel->isEnseignant()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce personnel n\'est pas un enseignant'
            ], 400);
        }

        // Vérifier si l'association existe déjà
        if ($personnel->classes()->where('classe_id', $request->classe_id)
                                   ->where('matiere_id', $request->matiere_id)
                                   ->where('annee_scolaire_id', $request->annee_scolaire_id)
                                   ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enseignant est déjà associé à cette classe pour cette matière et cette année scolaire'
            ], 400);
        }

        // Créer l'association avec tous les champs requis
        $personnel->classes()->attach($request->classe_id, [
            'matiere_id' => $request->matiere_id,
            'annee_scolaire_id' => $request->annee_scolaire_id,
            'role' => $request->role,
            'heures_semaine' => $request->heures_semaine,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => $request->statut,
            'notes_pedagogiques' => $request->notes_pedagogiques
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Classe associée avec succès à l\'enseignant',
            'data' => [
                'personnel' => $personnel->fresh(['classes']),
                'association' => $personnel->classes()->where('classe_id', $request->classe_id)
                                           ->where('matiere_id', $request->matiere_id)
                                           ->where('annee_scolaire_id', $request->annee_scolaire_id)
                                           ->first()
            ]
        ]);
    }

    /**
     * Dissocier une classe d'un enseignant
     */
    public function dissocierClasse(int $id, int $classeId, Request $request)
    {
        $request->validate([
            'matiere_id' => 'required|integer|exists:matieres,id',
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id'
        ]);

        $personnel = Personnel::findOrFail($id);
        
        if (!$personnel->isEnseignant()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce personnel n\'est pas un enseignant'
            ], 400);
        }

        if (!$personnel->classes()->where('classe_id', $classeId)
                                   ->where('matiere_id', $request->matiere_id)
                                   ->where('annee_scolaire_id', $request->annee_scolaire_id)
                                   ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enseignant n\'est pas associé à cette classe pour cette matière et cette année scolaire'
            ], 400);
        }

        // Dissocier en spécifiant tous les champs de la relation
        $personnel->classes()->wherePivot('classe_id', $classeId)
                           ->wherePivot('matiere_id', $request->matiere_id)
                           ->wherePivot('annee_scolaire_id', $request->annee_scolaire_id)
                           ->detach();

        return response()->json([
            'success' => true,
            'message' => 'Classe dissociée avec succès de l\'enseignant',
            'data' => [
                'personnel' => $personnel->fresh(['classes'])
            ]
        ]);
    }

    /**
     * Obtenir les classes d'un enseignant
     */
    public function classesEnseignant(int $id)
    {
        $personnel = Personnel::findOrFail($id);
        
        if (!$personnel->isEnseignant()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce personnel n\'est pas un enseignant'
            ], 400);
        }

        $classes = $personnel->classes()->with(['niveaux', 'programmes'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'personnel' => [
                    'id' => $personnel->id,
                    'nom_complet' => $personnel->nom_complet,
                    'type_personnel' => $personnel->type_personnel,
                    'fonction' => $personnel->fonction
                ],
                'classes' => $classes
            ]
        ]);
    }
}
