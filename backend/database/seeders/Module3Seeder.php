<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module3Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('👩‍🏫 Module3Seeder: enseignants ↔ matières ↔ classes (idempotent)...');

        $anneeId = DB::table('annees_scolaires')->where('code','2024-2025')->value('id');

        // Choisir (ou créer) un personnel minimal
        $personnelId = DB::table('personnels')->value('id');
        if (!$personnelId) {
            $personnelId = DB::table('personnels')->insertGetId([
                'matricule' => 'ENS-0001',
                'prenom' => 'Prof',
                'nom_famille' => 'Exemple',
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Prendre deux matières existantes (FR, MATH) si présentes
        $matiereFrId = DB::table('matieres')->where('code','FR')->value('id');
        $matiereMathId = DB::table('matieres')->where('code','MATH')->value('id');

        if ($matiereFrId) {
            DB::table('enseignants_matieres')->updateOrInsert(
                ['personnel_id' => $personnelId, 'matiere_id' => $matiereFrId],
                ['niveau_competence' => 'expert', 'est_actif' => 1]
            );
        }
        if ($matiereMathId) {
            DB::table('enseignants_matieres')->updateOrInsert(
                ['personnel_id' => $personnelId, 'matiere_id' => $matiereMathId],
                ['niveau_competence' => 'intermediaire', 'est_actif' => 1]
            );
        }

        // Lier à deux classes: CP-A Français et CP-A Arabe si existent
        $classeFr = DB::table('classes')->where('code','CP-A-FR-2024')->value('id');
        $classeAr = DB::table('classes')->where('code','CP-A-AR-2024')->value('id');

        if ($classeFr && $matiereFrId) {
            DB::table('enseignants_classes')->updateOrInsert(
                [
                    'personnel_id' => $personnelId,
                    'classe_id' => $classeFr,
                    'matiere_id' => $matiereFrId,
                    'annee_scolaire_id' => $anneeId,
                ],
                ['role' => 'titulaire', 'heures_semaine' => 6, 'statut' => 'actif']
            );
        }
        if ($classeAr && $matiereFrId) { // ex: même prof enseigne FR en classe arabe
            DB::table('enseignants_classes')->updateOrInsert(
                [
                    'personnel_id' => $personnelId,
                    'classe_id' => $classeAr,
                    'matiere_id' => $matiereFrId,
                    'annee_scolaire_id' => $anneeId,
                ],
                ['role' => 'titulaire', 'heures_semaine' => 4, 'statut' => 'actif']
            );
        }

        $this->command?->info('✅ Module3Seeder terminé.');
    }
}
