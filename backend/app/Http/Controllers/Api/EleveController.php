<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\ParentModel;
use App\Services\IdentifiantGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EleveController extends Controller
{
    /**
     * Afficher la liste des élèves.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Eleve::query();

            // Filtrage par statut
            if ($request->has('est_actif')) {
                $query->where('est_actif', $request->boolean('est_actif'));
            }

            // Filtrage par niveau
            if ($request->has('niveau_id')) {
                $query->where('niveau_id', $request->get('niveau_id'));
            }

            // Recherche par nom, prénom ou matricule
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('nom_famille', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'nom_famille');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $eleves = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des élèves récupérée avec succès',
                'data' => $eleves
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
     * Afficher un élève spécifique.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $eleve = Eleve::with(['parents', 'inscriptions'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Élève récupéré avec succès',
                'data' => $eleve
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Élève non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer un nouvel élève avec gestion des parents.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validation des données de l'élève
            $validator = Validator::make($request->all(), [
                // Données de l'élève (complètes)
                'prenom' => 'required|string|max:255',
                'nom_famille' => 'required|string|max:255',
                'civilite' => 'nullable|in:M,Mme,Mlle',
                'date_naissance' => 'required|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'sexe' => 'required|in:M,F',
                'infos_sante' => 'nullable|string',
                'photo' => 'nullable|string',
                'documents_obligatoires' => 'nullable|json',
                'observations_pedagogiques' => 'nullable|string',
                
                // Données des parents (optionnelles)
                'parents_existants' => 'nullable|array',
                'parents_existants.*.parent_id' => 'required_with:parents_existants|exists:parents,id',
                'parents_existants.*.role' => 'required_with:parents_existants|in:pere,mere,tuteur',
                'parents_existants.*.est_responsable_legal' => 'boolean',
                'parents_existants.*.ordre_priorite' => 'integer|min:1',
                
                'nouveaux_parents' => 'nullable|array',
                'nouveaux_parents.*.prenom' => 'required_with:nouveaux_parents|string|max:255',
                'nouveaux_parents.*.nom_famille' => 'required_with:nouveaux_parents|string|max:255',
                'nouveaux_parents.*.civilite' => 'required_with:nouveaux_parents|in:M,Mme,Mlle',
                'nouveaux_parents.*.date_naissance' => 'nullable|date',
                'nouveaux_parents.*.lieu_naissance' => 'nullable|string|max:255',
                'nouveaux_parents.*.telephone' => 'nullable|string|max:20',
                'nouveaux_parents.*.telephone_urgence' => 'nullable|string|max:20',
                'nouveaux_parents.*.email' => 'nullable|email',
                'nouveaux_parents.*.adresse' => 'nullable|string',
                'nouveaux_parents.*.ville' => 'nullable|string|max:255',
                'nouveaux_parents.*.code_postal' => 'nullable|string|max:10',
                'nouveaux_parents.*.pays' => 'nullable|string|max:255',
                'nouveaux_parents.*.profession' => 'nullable|string|max:255',
                'nouveaux_parents.*.employeur' => 'nullable|string|max:255',
                'nouveaux_parents.*.telephone_bureau' => 'nullable|string|max:20',
                'nouveaux_parents.*.role' => 'required_with:nouveaux_parents|in:pere,mere,tuteur',
                'nouveaux_parents.*.est_responsable_legal' => 'boolean',
                'nouveaux_parents.*.ordre_priorite' => 'integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                // 1. Générer le matricule de l'élève
                $matriculeEleve = IdentifiantGeneratorService::generateEleveIdentifiant();

                // 2. Créer l'élève avec toutes ses données
                $eleve = Eleve::create([
                    'matricule' => $matriculeEleve,
                    'prenom' => $request->input('prenom'),
                    'nom_famille' => $request->input('nom_famille'),
                    'civilite' => $request->input('civilite'),
                    'date_naissance' => $request->input('date_naissance'),
                    'lieu_naissance' => $request->input('lieu_naissance'),
                    'sexe' => $request->input('sexe'),
                    'infos_sante' => $request->input('infos_sante'),
                    'photo' => $request->input('photo'),
                    'documents_obligatoires' => $request->input('documents_obligatoires'),
                    'observations_pedagogiques' => $request->input('observations_pedagogiques'),
                ]);

                $parentsAssocies = [];

                // 3. Associer les parents existants
                if ($request->has('parents_existants')) {
                    foreach ($request->input('parents_existants') as $parentData) {
                        DB::table('eleves_parents')->insert([
                            'eleve_id' => $eleve->id,
                            'parent_id' => $parentData['parent_id'],
                            'role' => $parentData['role'],
                            'est_responsable_legal' => $parentData['est_responsable_legal'] ?? false,
                            'ordre_priorite' => $parentData['ordre_priorite'] ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        $parent = ParentModel::find($parentData['parent_id']);
                        $parentsAssocies[] = [
                            'id' => $parent->id,
                            'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille,
                            'role' => $parentData['role'],
                            'type' => 'existant'
                        ];
                    }
                }

                // 4. Créer et associer les nouveaux parents
                if ($request->has('nouveaux_parents')) {
                    foreach ($request->input('nouveaux_parents') as $parentData) {
                        $parent = ParentModel::create([
                            'prenom' => $parentData['prenom'],
                            'nom_famille' => $parentData['nom_famille'],
                            'civilite' => $parentData['civilite'],
                            'date_naissance' => $parentData['date_naissance'] ?? null,
                            'lieu_naissance' => $parentData['lieu_naissance'] ?? null,
                            'telephone' => $parentData['telephone'] ?? null,
                            'telephone_urgence' => $parentData['telephone_urgence'] ?? null,
                            'email' => $parentData['email'] ?? null,
                            'adresse' => $parentData['adresse'] ?? null,
                            'ville' => $parentData['ville'] ?? null,
                            'code_postal' => $parentData['code_postal'] ?? null,
                            'pays' => $parentData['pays'] ?? null,
                            'profession' => $parentData['profession'] ?? null,
                            'employeur' => $parentData['employeur'] ?? null,
                            'telephone_bureau' => $parentData['telephone_bureau'] ?? null,
                            'est_actif' => true
                        ]);

                        DB::table('eleves_parents')->insert([
                            'eleve_id' => $eleve->id,
                            'parent_id' => $parent->id,
                            'role' => $parentData['role'],
                            'est_responsable_legal' => $parentData['est_responsable_legal'] ?? false,
                            'ordre_priorite' => $parentData['ordre_priorite'] ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        $parentsAssocies[] = [
                            'id' => $parent->id,
                            'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille,
                            'role' => $parentData['role'],
                            'type' => 'nouveau'
                        ];
                    }
                }

                // 5. Charger les relations pour la réponse
                $eleve->load('parents');

                Log::info('Élève créé avec parents', [
                    'eleve_id' => $eleve->id,
                    'matricule' => $matriculeEleve,
                    'parents_count' => count($parentsAssocies)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Élève créé avec succès',
                    'data' => [
                        'eleve' => [
                            'id' => $eleve->id,
                            'matricule' => $eleve->matricule,
                            'nom_complet' => $eleve->prenom . ' ' . $eleve->nom_famille,
                            'date_naissance' => $eleve->date_naissance,
                            'photo' => $eleve->photo,
                            'parents' => $parentsAssocies
                        ],
                        'message_compte' => 'L\'élève a été créé. Le Super-Admin peut maintenant créer les comptes utilisateurs.'
                    ]
                ], 201);

            });

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'élève', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'élève',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un élève.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $eleve = Eleve::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'matricule' => 'sometimes|required|string|max:50|unique:eleves,matricule,' . $id,
                'nom_famille' => 'sometimes|required|string|max:255',
                'prenom' => 'sometimes|required|string|max:255',
                'date_naissance' => 'sometimes|required|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'civilite' => 'sometimes|required|in:garcon,fille',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email',
                'groupe_sanguin' => 'nullable|string|max:10',
                'allergies' => 'nullable|string',
                'maladies_chroniques' => 'nullable|string',
                'medecin_traitant' => 'nullable|string|max:255',
                'telephone_medecin' => 'nullable|string|max:20',
                'photo' => 'nullable|string',
                'documents_obligatoires' => 'nullable|json',
                'observations_pedagogiques' => 'nullable|string',
                'observations_medicales' => 'nullable|string',
                'est_actif' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $eleve->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Élève mis à jour avec succès',
                'data' => $eleve
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'élève',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un élève.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $eleve = Eleve::findOrFail($id);

            // Vérifier si l'élève a des inscriptions actives
            if ($eleve->inscriptions()->where('est_actif', true)->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet élève car il a des inscriptions actives'
                ], 400);
            }

            $eleve->delete();

            return response()->json([
                'success' => true,
                'message' => 'Élève supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'élève',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un élève.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $eleve = Eleve::findOrFail($id);
            $eleve->est_actif = !$eleve->est_actif;
            $eleve->save();

            $status = $eleve->est_actif ? 'activé' : 'désactivé';

            return response()->json([
                'success' => true,
                'message' => "Élève {$status} avec succès",
                'data' => $eleve
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
     * Récupérer les élèves actifs.
     */
    public function actifs(): JsonResponse
    {
        try {
            $eleves = Eleve::where('est_actif', true)
                         ->orderBy('nom_famille')
                         ->get();

            return response()->json([
                'success' => true,
                'message' => 'Élèves actifs récupérés avec succès',
                'data' => $eleves
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des élèves actifs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les élèves par niveau.
     */
    public function parNiveau(int $niveauId): JsonResponse
    {
        try {
            $eleves = Eleve::where('niveau_id', $niveauId)
                         ->where('est_actif', true)
                         ->orderBy('nom_famille')
                         ->get();

            return response()->json([
                'success' => true,
                'message' => 'Élèves du niveau récupérés avec succès',
                'data' => $eleves
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des élèves par niveau',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les parents d'un élève.
     */
    public function parents(int $id): JsonResponse
    {
        try {
            $eleve = Eleve::with('parents')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Parents de l\'élève récupérés avec succès',
                'data' => $eleve->parents
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
     * Récupérer les inscriptions d'un élève.
     */
    public function inscriptions(int $id): JsonResponse
    {
        try {
            $eleve = Eleve::with('inscriptions')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Inscriptions de l\'élève récupérées avec succès',
                'data' => $eleve->inscriptions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des inscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher des parents existants pour l'inscription d'un élève.
     */
    public function rechercherParents(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:2',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $search = $request->input('search');
            $limit = $request->input('limit', 10);

            $parents = ParentModel::where(function ($query) use ($search) {
                $query->where('prenom', 'like', "%{$search}%")
                      ->orWhere('nom_famille', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('telephone', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(prenom, ' ', nom_famille) LIKE ?", ["%{$search}%"]);
            })
            ->where('est_actif', true)
            ->select('id', 'prenom', 'nom_famille', 'civilite', 'email', 'telephone', 'profession')
            ->limit($limit)
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille,
                    'civilite' => $parent->civilite,
                    'email' => $parent->email,
                    'telephone' => $parent->telephone,
                    'profession' => $parent->profession
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Recherche de parents effectuée',
                'data' => $parents
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche de parents', [
                'error' => $e->getMessage(),
                'search' => $request->input('search')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de parents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // INSCRIPTION COMPLÈTE ÉLÈVE + PARENTS
    // ========================================

    /**
     * Inscription complète d'un élève avec ses parents
     */
    public function inscriptionComplete(Request $request): JsonResponse
    {
        try {
            // Validation des données de l'élève
            $validatorEleve = Validator::make($request->all(), [
                // Données de l'élève
                'eleve.prenom' => 'required|string|max:255',
                'eleve.nom_famille' => 'required|string|max:255',
                'eleve.date_naissance' => 'required|date',
                'eleve.lieu_naissance' => 'nullable|string|max:255',
                'eleve.sexe' => 'required|in:M,F',
                'eleve.infos_sante' => 'nullable|string',
                'eleve.observations_pedagogiques' => 'nullable|string',
                
                // Données des parents (au moins un parent requis)
                'parents' => 'required|array|min:1',
                'parents.*.prenom' => 'required|string|max:255',
                'parents.*.nom_famille' => 'required|string|max:255',
                'parents.*.civilite' => 'required|in:M,Mme,Mlle',
                'parents.*.date_naissance' => 'nullable|date',
                'parents.*.lieu_naissance' => 'nullable|string|max:255',
                'parents.*.telephone' => 'nullable|string|max:20',
                'parents.*.telephone_urgence' => 'nullable|string|max:20',
                'parents.*.email' => 'nullable|email',
                'parents.*.adresse' => 'nullable|string',
                'parents.*.ville' => 'nullable|string|max:255',
                'parents.*.code_postal' => 'nullable|string|max:10',
                'parents.*.pays' => 'nullable|string|max:255',
                'parents.*.profession' => 'nullable|string|max:255',
                'parents.*.employeur' => 'nullable|string|max:255',
                'parents.*.telephone_bureau' => 'nullable|string|max:20',
                'parents.*.role' => 'required|in:pere,mere,tuteur',
                'parents.*.est_responsable_legal' => 'boolean',
                'parents.*.ordre_priorite' => 'integer|min:1'
            ]);

            if ($validatorEleve->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $validatorEleve->errors()
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                // 1. Générer le matricule de l'élève
                $matriculeEleve = IdentifiantGeneratorService::generateEleveIdentifiant();

                // 2. Créer l'élève
                $eleve = Eleve::create([
                    'matricule' => $matriculeEleve,
                    'prenom' => $request->input('eleve.prenom'),
                    'nom_famille' => $request->input('eleve.nom_famille'),
                    'date_naissance' => $request->input('eleve.date_naissance'),
                    'lieu_naissance' => $request->input('eleve.lieu_naissance'),
                    'sexe' => $request->input('eleve.sexe'),
                    'infos_sante' => $request->input('eleve.infos_sante'),
                    'observations_pedagogiques' => $request->input('eleve.observations_pedagogiques'),
                ]);

                // 3. Créer les parents et les associer
                $parentsCrees = [];
                foreach ($request->input('parents') as $parentData) {
                    $parent = ParentModel::create([
                        'prenom' => $parentData['prenom'],
                        'nom_famille' => $parentData['nom_famille'],
                        'civilite' => $parentData['civilite'],
                        'date_naissance' => $parentData['date_naissance'] ?? null,
                        'lieu_naissance' => $parentData['lieu_naissance'] ?? null,
                        'telephone' => $parentData['telephone'] ?? null,
                        'telephone_urgence' => $parentData['telephone_urgence'] ?? null,
                        'email' => $parentData['email'] ?? null,
                        'adresse' => $parentData['adresse'] ?? null,
                        'ville' => $parentData['ville'] ?? null,
                        'code_postal' => $parentData['code_postal'] ?? null,
                        'pays' => $parentData['pays'] ?? null,
                        'profession' => $parentData['profession'] ?? null,
                        'employeur' => $parentData['employeur'] ?? null,
                        'telephone_bureau' => $parentData['telephone_bureau'] ?? null,
                        'est_actif' => true
                    ]);

                    // 4. Associer le parent à l'élève
                    DB::table('eleves_parents')->insert([
                        'eleve_id' => $eleve->id,
                        'parent_id' => $parent->id,
                        'role' => $parentData['role'],
                        'est_responsable_legal' => $parentData['est_responsable_legal'] ?? false,
                        'ordre_priorite' => $parentData['ordre_priorite'] ?? 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $parentsCrees[] = $parent;
                }

                // 5. Charger les relations pour la réponse
                $eleve->load('parents');

                Log::info('Inscription complète élève + parents réussie', [
                    'eleve_id' => $eleve->id,
                    'matricule' => $matriculeEleve,
                    'parents_count' => count($parentsCrees)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Inscription complète réussie',
                    'data' => [
                        'eleve' => [
                            'id' => $eleve->id,
                            'matricule' => $eleve->matricule,
                            'nom_complet' => $eleve->prenom . ' ' . $eleve->nom_famille,
                            'date_naissance' => $eleve->date_naissance,
                            'parents' => $eleve->parents->map(function ($parent) {
                                return [
                                    'id' => $parent->id,
                                    'nom_complet' => $parent->prenom . ' ' . $parent->nom_famille,
                                    'role' => $parent->pivot->role,
                                    'est_responsable_legal' => $parent->pivot->est_responsable_legal,
                                    'telephone' => $parent->telephone,
                                    'email' => $parent->email
                                ];
                            })
                        ],
                        'message_compte' => 'L\'élève et ses parents ont été inscrits. Le Super-Admin peut maintenant créer leurs comptes utilisateurs.'
                    ]
                ], 201);

            });

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inscription complète', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription complète',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
