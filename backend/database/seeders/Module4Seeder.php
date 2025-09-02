<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📚 Seeding Module 4: Matières et Programmes...');

        // 1. Liaison matières-niveaux
        $this->seedMatieresNiveaux();
        
        // 2. Liaison programmes-niveaux
        $this->seedProgrammesNiveaux();

        $this->command->info('✅ Module 4 seeded successfully!');
    }

    private function seedMatieresNiveaux(): void
    {
        $this->command->info('  🔗 Seeding matieres_niveaux...');
        
        $matieresNiveaux = [
            // Programme Français - CP-A
            ['matiere_id' => 1, 'niveau_id' => 1, 'programme_id' => 1, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 2, 'heures_semaine' => 5, 'heures_annee' => 180, 'ordre_matiere' => 1, 'est_obligatoire' => true, 'est_evaluee' => true],
            ['matiere_id' => 3, 'niveau_id' => 1, 'programme_id' => 1, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 3, 'heures_semaine' => 8, 'heures_annee' => 288, 'ordre_matiere' => 2, 'est_obligatoire' => true, 'est_evaluee' => true],
            ['matiere_id' => 4, 'niveau_id' => 1, 'programme_id' => 1, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 2, 'heures_semaine' => 4, 'heures_annee' => 144, 'ordre_matiere' => 3, 'est_obligatoire' => true, 'est_evaluee' => true],
            
            // Programme Arabe - CP-A
            ['matiere_id' => 5, 'niveau_id' => 6, 'programme_id' => 2, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 3, 'heures_semaine' => 6, 'heures_annee' => 216, 'ordre_matiere' => 1, 'est_obligatoire' => true, 'est_evaluee' => true],
            ['matiere_id' => 6, 'niveau_id' => 6, 'programme_id' => 2, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 2, 'heures_semaine' => 4, 'heures_annee' => 144, 'ordre_matiere' => 2, 'est_obligatoire' => true, 'est_evaluee' => true],
            
            // Programme Français - CE1-A
            ['matiere_id' => 1, 'niveau_id' => 2, 'programme_id' => 1, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 2, 'heures_semaine' => 5, 'heures_annee' => 180, 'ordre_matiere' => 1, 'est_obligatoire' => true, 'est_evaluee' => true],
            ['matiere_id' => 3, 'niveau_id' => 2, 'programme_id' => 1, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 3, 'heures_semaine' => 8, 'heures_annee' => 288, 'ordre_matiere' => 2, 'est_obligatoire' => true, 'est_evaluee' => true],
            
            // Programme Arabe - CE1-A
            ['matiere_id' => 5, 'niveau_id' => 7, 'programme_id' => 2, 'annee_scolaire_id' => 1, 'coefficient_niveau' => 3, 'heures_semaine' => 6, 'heures_annee' => 216, 'ordre_matiere' => 1, 'est_obligatoire' => true, 'est_evaluee' => true],
        ];

        foreach ($matieresNiveaux as $data) {
            DB::table('matieres_niveaux')->insert(array_merge($data, [
                'statut' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function seedProgrammesNiveaux(): void
    {
        $this->command->info('  🎯 Seeding programmes_niveaux...');
        
        $programmesNiveaux = [
            // Programme Français
            ['programme_id' => 1, 'niveau_id' => 1, 'annee_scolaire_id' => 1, 'ordre_progression' => 1, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 25, 'heures_total_annee' => 900, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
            ['programme_id' => 1, 'niveau_id' => 2, 'annee_scolaire_id' => 1, 'ordre_progression' => 2, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 26, 'heures_total_annee' => 936, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
            ['programme_id' => 1, 'niveau_id' => 3, 'annee_scolaire_id' => 1, 'ordre_progression' => 3, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 27, 'heures_total_annee' => 972, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
            
            // Programme Arabe
            ['programme_id' => 2, 'niveau_id' => 6, 'annee_scolaire_id' => 1, 'ordre_progression' => 1, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 20, 'heures_total_annee' => 720, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
            ['programme_id' => 2, 'niveau_id' => 7, 'annee_scolaire_id' => 1, 'ordre_progression' => 2, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 21, 'heures_total_annee' => 756, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
            ['programme_id' => 2, 'niveau_id' => 8, 'annee_scolaire_id' => 1, 'ordre_progression' => 3, 'duree_niveau' => 1, 'nombre_eleves_max' => 25, 'nombre_eleves_min' => 15, 'heures_total_semaine' => 22, 'heures_total_annee' => 792, 'est_niveau_obligatoire' => true, 'est_niveau_test' => false],
        ];

        foreach ($programmesNiveaux as $data) {
            DB::table('programmes_niveaux')->insert(array_merge($data, [
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
