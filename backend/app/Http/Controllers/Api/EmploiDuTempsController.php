<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmploiDuTempsCours;
use App\Models\Classe;
use App\Models\Matiere;
use App\Models\Personnel;
use App\Models\Salle;
use App\Models\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmploiDuTempsController extends Controller
{
    public function index(Request $request)
    {
        $query = EmploiDuTempsCours::with([
            'classe.niveau.programme',
            'matiere',
            'enseignant',
            'salle',
            'anneeScolaire',
            'jourSemaine',
            'plageHoraire'
        ]);

        if ($request->has('classe_id')) {
            $query->where('classe_id', $request->classe_id);
        }

        if ($request->has('enseignant_id')) {
            $query->where('enseignant_id', $request->enseignant_id);
        }

        $emplois = $query->orderBy('jour_semaine_id')
                        ->orderBy('plage_horaire_id')
                        ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $emplois
        ]);
    }

    public function show(int $id)
    {
        $emploi = EmploiDuTempsCours::with([
            'classe.niveau.programme',
            'matiere',
            'enseignant',
            'salle',
            'anneeScolaire',
            'jourSemaine',
            'plageHoraire'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $emploi
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'classe_id' => 'required|integer|exists:classes,id',
            'jour_semaine_id' => 'required|integer|exists:jours_semaine,id',
            'plage_horaire_id' => 'required|integer|exists:plages_horaires,id',
            'matiere_id' => 'required|integer|exists:matieres,id',
            'enseignant_id' => 'required|integer|exists:personnels,id',
            'salle_id' => 'nullable|integer|exists:salles,id',
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id',
            'est_exception' => 'boolean',
            'observations' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $emploi = EmploiDuTempsCours::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Emploi du temps créé avec succès',
            'data' => $emploi->load([
                'classe.niveau.programme',
                'matiere',
                'enseignant',
                'salle',
                'anneeScolaire',
                'jourSemaine',
                'plageHoraire'
            ])
        ], 201);
    }

    public function emploiClasse(int $classeId, Request $request)
    {
        $anneeScolaireId = $request->get('annee_scolaire_id');
        
        $query = EmploiDuTempsCours::with([
            'matiere',
            'enseignant',
            'salle',
            'jourSemaine',
            'plageHoraire'
        ])->where('classe_id', $classeId);

        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }

        $emplois = $query->orderBy('jour_semaine_id')
                        ->orderBy('plage_horaire_id')
                        ->get();

        $emploisGroupes = $emplois->groupBy('jour_semaine.nom');

        return response()->json([
            'success' => true,
            'data' => [
                'classe_id' => $classeId,
                'emplois_par_jour' => $emploisGroupes
            ]
        ]);
    }

    public function emploiEnseignant(int $enseignantId, Request $request)
    {
        $anneeScolaireId = $request->get('annee_scolaire_id');
        
        $query = EmploiDuTempsCours::with([
            'classe.niveau.programme',
            'matiere',
            'salle',
            'jourSemaine',
            'plageHoraire'
        ])->where('enseignant_id', $enseignantId);

        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }

        $emplois = $query->orderBy('jour_semaine_id')
                        ->orderBy('plage_horaire_id')
                        ->get();

        $emploisGroupes = $emplois->groupBy('jour_semaine.nom');

        return response()->json([
            'success' => true,
            'data' => [
                'enseignant_id' => $enseignantId,
                'emplois_par_jour' => $emploisGroupes
 => $emploisGroupes
            ]
        ]);
    }
}
