<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('🌱 BaseDataSeeder: insertion idempotente des données de base...');

        // Programmes (FR, AR)
        $programmes = [
            ['code' => 'FR', 'nom' => 'Français', 'description' => 'Programme français', 'langue_principale' => 'fr', 'est_actif' => 1],
            ['code' => 'AR', 'nom' => 'Arabe', 'description' => 'Programme arabe', 'langue_principale' => 'ar', 'est_actif' => 1],
        ];
        foreach ($programmes as $p) {
            DB::table('programmes')->updateOrInsert(['code' => $p['code']], $p);
        }

        // Années scolaires (2024-2025 en cours)
        $annees = [
            ['code' => '2024-2025', 'nom' => '2024-2025', 'description' => 'Année scolaire 2024-2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-08-31', 'statut' => 'en_cours', 'est_annee_courante' => 1, 'est_annee_par_defaut' => 1, 'nombre_trimestres' => 3, 'nombre_semestres' => 2, 'est_actif' => 1],
        ];
        foreach ($annees as $a) {
            DB::table('annees_scolaires')->updateOrInsert(['code' => $a['code']], $a);
        }

        // Niveaux (CP..CM2) rattachés par défaut au programme FR (id = 1 via code FR)
        $programmeFrId = DB::table('programmes')->where('code', 'FR')->value('id');
        $niveaux = [
            ['code' => 'CP', 'nom' => 'CP', 'description' => 'Cours Préparatoire', 'ordre' => 1, 'age_min' => 6, 'age_max' => 7, 'capacite_max' => 25, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'CE1', 'nom' => 'CE1', 'description' => 'Cours Élémentaire 1', 'ordre' => 2, 'age_min' => 7, 'age_max' => 8, 'capacite_max' => 25, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'CE2', 'nom' => 'CE2', 'description' => 'Cours Élémentaire 2', 'ordre' => 3, 'age_min' => 8, 'age_max' => 9, 'capacite_max' => 25, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'CM1', 'nom' => 'CM1', 'description' => 'Cours Moyen 1', 'ordre' => 4, 'age_min' => 9, 'age_max' => 10, 'capacite_max' => 25, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'CM2', 'nom' => 'CM2', 'description' => 'Cours Moyen 2', 'ordre' => 5, 'age_min' => 10, 'age_max' => 11, 'capacite_max' => 25, 'programme_id' => $programmeFrId, 'est_actif' => 1],
        ];
        foreach ($niveaux as $n) {
            DB::table('niveaux')->updateOrInsert(['code' => $n['code']], $n);
        }

        // Matières de base
        $matieres = [
            ['code' => 'MATH', 'nom' => 'Mathématiques', 'description' => 'Mathématiques', 'couleur' => 'bleu', 'coefficient' => 4, 'ordre' => 1, 'est_matiere_principale' => 1, 'est_matiere_notes' => 1, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'FR',   'nom' => 'Français',      'description' => 'Langue française', 'couleur' => 'rouge', 'coefficient' => 4, 'ordre' => 2, 'est_matiere_principale' => 1, 'est_matiere_notes' => 1, 'programme_id' => $programmeFrId, 'est_actif' => 1],
            ['code' => 'AR',   'nom' => 'Arabe',         'description' => 'Langue arabe', 'couleur' => 'vert', 'coefficient' => 4, 'ordre' => 3, 'est_matiere_principale' => 1, 'est_matiere_notes' => 1, 'programme_id' => DB::table('programmes')->where('code', 'AR')->value('id'), 'est_actif' => 1],
        ];
        foreach ($matieres as $m) {
            DB::table('matieres')->updateOrInsert(['code' => $m['code']], $m);
        }

        $this->command?->info('✅ BaseDataSeeder terminé.');
    }
}
