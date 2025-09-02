<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscriptionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('📚 InscriptionsSeeder: académiques + financières (idempotent)...');

        $anneeId = DB::table('annees_scolaires')->where('code','2024-2025')->value('id');
        $eleveId = DB::table('eleves')->where('matricule','E001')->value('id');
        $parentId = DB::table('parents')->where('prenom','Fatima')->where('nom_famille','Ben Ali')->value('id');
        $classeFr = DB::table('classes')->where('code','CP-A-FR-2024')->value('id');
        $classeAr = DB::table('classes')->where('code','CP-A-AR-2024')->value('id');
        $grilleId = DB::table('grilles_tarifaires')->where('code','GRILLE_CP_2024_2025')->value('id');
        $personnelId = DB::table('personnels')->where('matricule','P001')->value('id');
        $modePaiementId = DB::table('modes_paiement')->where('code','VIREMENT')->value('id');

        // 1) Inscription académique (1 ligne = 1 élève/année avec 2 classes FR/AR)
        if ($eleveId && $anneeId && $classeFr && $classeAr) {
            DB::table('inscriptions_eleves')->updateOrInsert(
                ['eleve_id' => $eleveId, 'annee_scolaire_id' => $anneeId],
                [
                    'classe_francaise_id' => $classeFr,
                    'classe_arabe_id' => $classeAr,
                    'date_inscription' => '2024-06-15',
                    'type_inscription' => 'nouvelle',
                    'statut' => 'inscrit',
                    'notes_administratives' => 'Élève inscrit en CP bilingue',
                ]
            );
        }

        // 2) Inscription financière (1 ligne = 1 élève/année avec scolarité par niveau)
        if ($eleveId && $anneeId && $grilleId && $classeFr && $classeAr && $personnelId) {
            DB::table('inscriptions_financieres')->updateOrInsert(
                ['eleve_id' => $eleveId, 'annee_scolaire_id' => $anneeId],
                [
                    'grille_tarifaire_id' => $grilleId,
                    'numero_inscription' => 'FIN-2024-001',
                    'date_inscription' => '2024-06-15',
                    'statut' => 'validee',
                    'type_inscription' => 'nouvelle',
                    'inscription_francais' => 1,
                    'inscription_arabe' => 1,
                    'classe_francaise_id' => $classeFr,
                    'classe_arabe_id' => $classeAr,
                    'montant_total_ht' => 3000.00, // Scolarité CP
                    'montant_tva' => 0.00,
                    'montant_total_ttc' => 3000.00,
                    'montant_remise' => 0.00,
                    'montant_net_a_payer' => 3000.00,
                    'mode_echeances' => 'mensuel',
                    'nombre_echeances' => 10,
                    'montant_echeance' => 300.00,
                    'date_premiere_echeance' => '2024-09-01',
                    'pourcentage_remise' => 0.00,
                    'motif_remise' => null,
                    'bourse_accordee' => 0,
                    'montant_bourse' => 0.00,
                    'motif_bourse' => null,
                    'facture_generee' => 1,
                    'numero_facture' => 'FAC-2024-001',
                    'date_facture' => '2024-06-20',
                    'recu_genere' => 1,
                    'cree_par_id' => $personnelId,
                    'valide_par_id' => $personnelId,
                    'date_validation' => '2024-06-20',
                    'commentaires_validation' => 'Inscription financière validée',
                    'notes_administratives' => 'Paiement en 10 échéances mensuelles',
                    'montant_paye' => 0.00,
                    'montant_restant' => 3000.00,
                    'statut_paiement' => 'en_attente',
                    'date_dernier_paiement' => null,
                    'date_echeance_paiement' => '2024-09-01',
                    'details_frais' => json_encode([
                        'frais_scolarite_niveau' => [
                            'montant' => 3000.00,
                            'description' => 'Frais de scolarité CP 2024-2025',
                            'niveau' => 'CP'
                        ]
                    ]),
                    'historique_modifications' => json_encode([]),
                    'observations' => 'Inscription standard CP bilingue',
                ]
            );
        }

        // 3) Premier paiement (échéance 1)
        $inscriptionFinanciereId = DB::table('inscriptions_financieres')->where('eleve_id',$eleveId)->value('id');
        if ($inscriptionFinanciereId && $eleveId && $anneeId && $personnelId && $modePaiementId) {
            DB::table('paiements')->updateOrInsert(
                ['inscription_financiere_id' => $inscriptionFinanciereId, 'numero_echeance' => 1],
                [
                    'mode_paiement_id' => $modePaiementId,
                    'eleve_id' => $eleveId,
                    'annee_scolaire_id' => $anneeId,
                    'numero_paiement' => 'PAY-2024-001',
                    'type_paiement' => 'echeance',
                    'statut' => 'en_attente',
                    'statut_traitement' => 'non_traite',
                    'montant_demande' => 300.00,
                    'montant_paye' => 0.00,
                    'montant_restant' => 300.00,
                    'frais_transaction' => 0.00,
                    'montant_net' => 300.00,
                    'date_echeance' => '2024-09-01',
                    'date_paiement' => null,
                    'date_limite_paiement' => '2024-09-15',
                    'reference_paiement' => null,
                    'numero_transaction' => null,
                    'erreur_paiement' => null,
                    'motif_refus' => null,
                    'commentaires_banque' => null,
                    'nombre_tentatives' => 0,
                    'montant_remise' => 0.00,
                    'motif_remise' => null,
                    'montant_penalite' => 0.00,
                    'motif_penalite' => null,
                    'cree_par_id' => $personnelId,
                    'valide_par_id' => null,
                    'annule_par_id' => null,
                    'date_validation' => null,
                    'date_annulation' => null,
                    'commentaires_validation' => null,
                    'commentaires_annulation' => null,
                    'recu_genere' => 0,
                    'numero_recu' => null,
                    'fichier_recu' => null,
                    'facture_generee' => 0,
                    'numero_facture' => null,
                    'details_paiement' => json_encode([
                        'echeance' => 1,
                        'montant_echeance' => 300.00,
                        'date_echeance' => '2024-09-01'
                    ]),
                    'historique_statuts' => json_encode([
                        ['statut' => 'en_attente', 'date' => '2024-06-20', 'commentaire' => 'Création de l\'échéance']
                    ]),
                    'observations' => 'Première échéance de scolarité CP',
                    'metadonnees_techniques' => json_encode([]),
                    'ip_paiement' => null,
                    'user_agent' => null,
                    'hash_securite' => null,
                ]
            );
        }

        $this->command?->info('✅ InscriptionsSeeder terminé.');
    }
}
