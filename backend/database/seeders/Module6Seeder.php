<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module6Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('📝 Module6Seeder: périodes + évaluations minimales (idempotent)...');

        $anneeId = DB::table('annees_scolaires')->where('code','2024-2025')->value('id');
        $niveauCpId = DB::table('niveaux')->where('code','CP')->value('id');
        $progFrId = DB::table('programmes')->where('code','FR')->value('id');
        $enseignantId = DB::table('personnels')->value('id');
        if (!$enseignantId) {
            $enseignantId = DB::table('personnels')->insertGetId([
                'matricule' => 'ENS-0001',
                'prenom' => 'Prof',
                'nom_famille' => 'Exemple',
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1) Périodes de base (trimestres)
        $periodes = [
            ['code' => 'T1-2024', 'nom' => 'Trimestre 1', 'description' => 'T1 2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2024-12-15', 'ordre' => 1, 'est_actif' => 1, 'annee_scolaire_id' => $anneeId],
            ['code' => 'T2-2025', 'nom' => 'Trimestre 2', 'description' => 'T2 2024-2025', 'date_debut' => '2025-01-05', 'date_fin' => '2025-03-31', 'ordre' => 2, 'est_actif' => 1, 'annee_scolaire_id' => $anneeId],
            ['code' => 'T3-2025', 'nom' => 'Trimestre 3', 'description' => 'T3 2024-2025', 'date_debut' => '2025-04-01', 'date_fin' => '2025-06-30', 'ordre' => 3, 'est_actif' => 1, 'annee_scolaire_id' => $anneeId],
        ];
        foreach ($periodes as $p) {
            DB::table('periodes')->updateOrInsert(['code' => $p['code']], $p);
        }
        $periodeT1Id = DB::table('periodes')->where('code','T1-2024')->value('id');

        // 2) Evaluations minimales (si matières dispo)
        $matiereFrId = DB::table('matieres')->where('code','FR')->value('id');
        $matiereMathId = DB::table('matieres')->where('code','MATH')->value('id');

        $evaluations = [];
        if ($matiereFrId) {
            $evaluations[] = [
                'code' => 'EV-FR-CP-T1', 'nom' => 'Dictée T1 CP', 'type' => 'controle', 'categorie' => 'ecrit',
                'coefficient' => 1.0, 'note_maximale' => 20,
                'periode_id' => $periodeT1Id, 'matiere_id' => $matiereFrId, 'niveau_id' => $niveauCpId, 'programme_id' => $progFrId,
                'annee_scolaire_id' => $anneeId, 'date_evaluation' => '2024-10-15', 'statut' => 'planifie', 'est_obligatoire' => 1,
                'est_visible_parents' => 1, 'enseignant_id' => $enseignantId,
            ];
        }
        if ($matiereMathId) {
            $evaluations[] = [
                'code' => 'EV-MATH-CP-T1', 'nom' => 'Calcul T1 CP', 'type' => 'controle', 'categorie' => 'ecrit',
                'coefficient' => 1.0, 'note_maximale' => 20,
                'periode_id' => $periodeT1Id, 'matiere_id' => $matiereMathId, 'niveau_id' => $niveauCpId, 'programme_id' => $progFrId,
                'annee_scolaire_id' => $anneeId, 'date_evaluation' => '2024-11-10', 'statut' => 'planifie', 'est_obligatoire' => 1,
                'est_visible_parents' => 1, 'enseignant_id' => $enseignantId,
            ];
        }

        foreach ($evaluations as $e) {
            DB::table('evaluations')->updateOrInsert(['code' => $e['code']], $e);
        }

        $this->command?->info('✅ Module6Seeder terminé.');
    }
}
