<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module2Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('🏫 Module2Seeder: classes + liaisons idempotentes...');

        $anneeId = DB::table('annees_scolaires')->where('code', '2024-2025')->value('id');
        $progFr = DB::table('programmes')->where('code', 'FR')->value('id');
        $progAr = DB::table('programmes')->where('code', 'AR')->value('id');

        $niveaux = DB::table('niveaux')->whereIn('code', ['CP','CE1','CE2','CM1','CM2'])
            ->pluck('id','code');

        // Génère des classes FR et AR par niveau (suffixes A)
        $defs = [
            ['code' => 'CP',  'nomFr' => 'CP-A Français',  'nomAr' => 'CP-A Arabe'],
            ['code' => 'CE1', 'nomFr' => 'CE1-A Français', 'nomAr' => 'CE1-A Arabe'],
            ['code' => 'CE2', 'nomFr' => 'CE2-A Français', 'nomAr' => 'CE2-A Arabe'],
            ['code' => 'CM1', 'nomFr' => 'CM1-A Français', 'nomAr' => 'CM1-A Arabe'],
            ['code' => 'CM2', 'nomFr' => 'CM2-A Français', 'nomAr' => 'CM2-A Arabe'],
        ];

        foreach ($defs as $d) {
            $niveauId = $niveaux[$d['code']] ?? null;
            if (!$niveauId) { continue; }

            // Classe FR
            $codeClasseFr = $d['code'].'-A-FR-2024';
            DB::table('classes')->updateOrInsert(
                ['code' => $codeClasseFr],
                ['nom' => $d['nomFr'], 'capacite_max' => 30, 'statut' => 'active']
            );
            $classeFrId = DB::table('classes')->where('code', $codeClasseFr)->value('id');

            // Lien FR
            DB::table('classes_niveaux')->updateOrInsert(
                [
                    'classe_id' => $classeFrId,
                    'niveau_id' => $niveauId,
                    'programme_id' => $progFr,
                    'annee_scolaire_id' => $anneeId,
                ],
                ['ordre_affichage' => 1]
            );

            // Classe AR
            $codeClasseAr = $d['code'].'-A-AR-2024';
            DB::table('classes')->updateOrInsert(
                ['code' => $codeClasseAr],
                ['nom' => $d['nomAr'], 'capacite_max' => 30, 'statut' => 'active']
            );
            $classeArId = DB::table('classes')->where('code', $codeClasseAr)->value('id');

            // Lien AR
            DB::table('classes_niveaux')->updateOrInsert(
                [
                    'classe_id' => $classeArId,
                    'niveau_id' => $niveauId,
                    'programme_id' => $progAr,
                    'annee_scolaire_id' => $anneeId,
                ],
                ['ordre_affichage' => 1]
            );
        }

        $this->command?->info('✅ Module2Seeder terminé.');
    }
} 