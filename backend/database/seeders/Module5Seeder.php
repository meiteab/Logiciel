<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module5Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('📅 Module5Seeder: emplois du temps (idempotent)...');

        $anneeId = DB::table('annees_scolaires')->where('code','2024-2025')->value('id');
        $enseignantId = DB::table('personnels')->value('id');
        $matiereFrId = DB::table('matieres')->where('code','FR')->value('id');
        $classeFr = DB::table('classes')->where('code','CP-A-FR-2024')->value('id');
        $classeAr = DB::table('classes')->where('code','CP-A-AR-2024')->value('id');

        // 1) Jours (lun->ven) selon structure existante
        $jours = [
            ['code' => 'LUN', 'nom' => 'Lundi',    'nom_court' => 'Lun', 'numero_jour' => 1, 'est_jour_cours' => 1, 'est_actif' => 1],
            ['code' => 'MAR', 'nom' => 'Mardi',    'nom_court' => 'Mar', 'numero_jour' => 2, 'est_jour_cours' => 1, 'est_actif' => 1],
            ['code' => 'MER', 'nom' => 'Mercredi', 'nom_court' => 'Mer', 'numero_jour' => 3, 'est_jour_cours' => 1, 'est_actif' => 1],
            ['code' => 'JEU', 'nom' => 'Jeudi',    'nom_court' => 'Jeu', 'numero_jour' => 4, 'est_jour_cours' => 1, 'est_actif' => 1],
            ['code' => 'VEN', 'nom' => 'Vendredi', 'nom_court' => 'Ven', 'numero_jour' => 5, 'est_jour_cours' => 1, 'est_actif' => 1],
        ];
        foreach ($jours as $j) {
            DB::table('jours_semaine')->updateOrInsert(['code' => $j['code']], $j);
        }
        $lunId = DB::table('jours_semaine')->where('code','LUN')->value('id');
        $marId = DB::table('jours_semaine')->where('code','MAR')->value('id');

        // 2) Plages
        $plages = [
            ['code' => 'P1', 'nom' => '1ère Période', 'description' => null, 'heure_debut' => '08:00:00', 'heure_fin' => '10:00:00', 'duree_minutes' => 120, 'type' => 'cours', 'ordre' => 1, 'est_obligatoire' => 1, 'est_actif' => 1],
            ['code' => 'P2', 'nom' => '2ème Période', 'description' => null, 'heure_debut' => '10:15:00', 'heure_fin' => '12:15:00', 'duree_minutes' => 120, 'type' => 'cours', 'ordre' => 2, 'est_obligatoire' => 1, 'est_actif' => 1],
        ];
        foreach ($plages as $p) {
            DB::table('plages_horaires')->updateOrInsert(['code' => $p['code']], $p);
        }
        $p1Id = DB::table('plages_horaires')->where('code','P1')->value('id');
        $p2Id = DB::table('plages_horaires')->where('code','P2')->value('id');

        // 3) Salle (colonnes: code, nom, capacite_max)
        DB::table('salles')->updateOrInsert(
            ['code' => 'S-CP-A'],
            ['nom' => 'Salle CP-A', 'type' => 'classe', 'capacite_max' => 35, 'est_actif' => 1]
        );
        $salleId = DB::table('salles')->where('code','S-CP-A')->value('id');

        // 4) Cours (CP FR et CP AR)
        if ($classeFr && $matiereFrId && $enseignantId && $salleId && $lunId && $p1Id) {
            DB::table('emplois_du_temps_cours')->updateOrInsert(
                [
                    'classe_id' => $classeFr,
                    'jour_semaine_id' => $lunId,
                    'plage_horaire_id' => $p1Id,
                    'annee_scolaire_id' => $anneeId,
                ],
                [
                    'matiere_id' => $matiereFrId,
                    'enseignant_id' => $enseignantId,
                    'salle_id' => $salleId,
                    'type_cours' => 'cours',
                    'statut' => 'planifie',
                ]
            );
        }
        if ($classeAr && $matiereFrId && $enseignantId && $salleId && $marId && $p2Id) {
            DB::table('emplois_du_temps_cours')->updateOrInsert(
                [
                    'classe_id' => $classeAr,
                    'jour_semaine_id' => $marId,
                    'plage_horaire_id' => $p2Id,
                    'annee_scolaire_id' => $anneeId,
                ],
                [
                    'matiere_id' => $matiereFrId,
                    'enseignant_id' => $enseignantId,
                    'salle_id' => $salleId,
                    'type_cours' => 'cours',
                    'statut' => 'planifie',
                ]
            );
        }

        $this->command?->info('✅ Module5Seeder terminé.');
    }
}
