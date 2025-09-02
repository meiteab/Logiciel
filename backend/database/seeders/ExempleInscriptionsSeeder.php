<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExempleInscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // EXEMPLE : Un élève en CP-A avec 1 seule ligne d'inscription
        
        // 1. Inscription en CP-A (français ET arabe) - 1 seule ligne
        DB::table('inscriptions_eleves')->insert([
            'eleve_id' => 1, // Élève ID 1
            'classe_francaise_id' => 1, // CP-A Français
            'classe_arabe_id' => 6, // CP-A Arabe
            'annee_scolaire_id' => 1, // 2024-2025
            'date_inscription' => '2024-09-01',
            'type_inscription' => 'nouvelle',
            'statut' => 'inscrit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // 2. Réinscription année suivante : CE1-A (français ET arabe) - 1 seule ligne
        DB::table('inscriptions_eleves')->insert([
            'eleve_id' => 1, // Même élève
            'classe_francaise_id' => 3, // CE1-A Français
            'classe_arabe_id' => 8, // CE1-A Arabe
            'annee_scolaire_id' => 2, // 2025-2026
            'date_inscription' => '2025-09-01',
            'type_inscription' => 'reinscription',
            'statut' => 'inscrit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('✅ EXEMPLE D\'INSCRIPTIONS CRÉÉ !');
        $this->command->info('');
        $this->command->info('📚 Élève ID 1 en CP-A (2024-2025) :');
        $this->command->info('   ├── classe_francaise_id = 1 (CP-A Français)');
        $this->command->info('   └── classe_arabe_id = 6 (CP-A Arabe)');
        $this->command->info('   → 1 SEULE LIGNE dans la table !');
        $this->command->info('');
        $this->command->info('📚 Élève ID 1 en CE1-A (2025-2026) :');
        $this->command->info('   ├── classe_francaise_id = 3 (CE1-A Français)');
        $this->command->info('   └── classe_arabe_id = 8 (CE1-A Arabe)');
        $this->command->info('   → 1 SEULE LIGNE dans la table !');
        $this->command->info('');
        $this->command->info('🎯 AVANTAGE : 1 formulaire d\'inscription = 1 ligne en base !');
    }
} 