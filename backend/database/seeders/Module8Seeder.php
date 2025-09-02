<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module8Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('💰 Module8Seeder: grilles & tarifs par niveau (idempotent)...');

        // 1) Types de frais
        $typesFrais = [
            ['code' => 'SCOLARITE', 'nom' => 'Frais de scolarité', 'description' => 'Frais de scolarité annuels', 'categorie' => 'scolarite', 'est_obligatoire' => 1, 'est_actif' => 1],
            ['code' => 'CANTINE', 'nom' => 'Frais de cantine', 'description' => 'Frais de restauration scolaire', 'categorie' => 'services', 'est_obligatoire' => 0, 'est_actif' => 1],
        ];
        foreach ($typesFrais as $tf) {
            DB::table('types_frais')->updateOrInsert(['code' => $tf['code']], $tf);
        }

        // 2) Modes de paiement
        $modesPaiement = [
            ['code' => 'VIREMENT', 'nom' => 'Virement bancaire', 'description' => 'Paiement par virement bancaire', 'est_actif' => 1],
            ['code' => 'CHEQUE', 'nom' => 'Chèque', 'description' => 'Paiement par chèque', 'est_actif' => 1],
            ['code' => 'ESPECES', 'nom' => 'Espèces', 'description' => 'Paiement en espèces', 'est_actif' => 1],
        ];
        foreach ($modesPaiement as $mp) {
            DB::table('modes_paiement')->updateOrInsert(['code' => $mp['code']], $mp);
        }

        // 3) Grilles tarifaires par niveau
        $niveaux = DB::table('niveaux')->get();
        foreach ($niveaux as $niveau) {
            $codeGrille = 'GRILLE_' . strtoupper($niveau->code) . '_2024_2025';
            $nomGrille = 'Grille ' . $niveau->nom . ' 2024-2025';
            
            DB::table('grilles_tarifaires')->updateOrInsert(
                ['code' => $codeGrille],
                [
                    'nom' => $nomGrille,
                    'description' => 'Grille tarifaire ' . $niveau->nom . ' pour l\'année 2024-2025',
                    'niveau_id' => $niveau->id,
                    'annee_scolaire_id' => DB::table('annees_scolaires')->where('code', '2024-2025')->value('id'),
                    'date_debut_validite' => '2024-09-01',
                    'date_fin_validite' => '2025-08-31',
                    'est_grille_par_defaut' => 1,
                    'est_actif' => 1,
                ]
            );
            
            $grilleId = DB::table('grilles_tarifaires')->where('code', $codeGrille)->value('id');
            
            // 4) Tarifs pour cette grille
            $montantScolarite = 3000 + ($niveau->ordre * 200); // CP: 3000€, CE1: 3200€, etc.
            
            DB::table('tarifs')->updateOrInsert(
                ['grille_tarifaire_id' => $grilleId, 'type_frais_id' => DB::table('types_frais')->where('code', 'SCOLARITE')->value('id'), 'date_debut_validite' => '2024-09-01'],
                [
                    'montant_ht' => $montantScolarite,
                    'montant_ttc' => $montantScolarite,
                    'taux_tva' => 0.00,
                    'devise' => 'EUR',
                    'type_calcul' => 'fixe',
                    'valeur_calcul' => $montantScolarite,
                    'date_fin_validite' => '2025-08-31',
                    'notes' => 'Tarif scolarité ' . $niveau->nom . ' 2024-2025',
                    'est_actif' => 1,
                ]
            );
        }

        $this->command?->info('✅ Module8Seeder terminé.');
    }
}
