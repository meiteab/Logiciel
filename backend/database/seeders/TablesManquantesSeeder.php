<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TablesManquantesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedCategoriesDepenses();
        $this->seedDepenses();
        $this->seedAbsencesEleves();
        $this->seedMessages();
        $this->seedAnnonces();
    }

    private function seedCategoriesDepenses(): void
    {
        $categories = [
            [
                'code' => 'MATERIEL_PEDAGOGIQUE',
                'nom' => 'Matériel pédagogique',
                'description' => 'Achats de matériel pédagogique pour les classes',
                'type' => 'pedagogique',
                'budget_annuel' => 50000.00,
                'seuil_alerte' => 40000.00,
                'budget_renouvelable' => true,
                'validation_obligatoire' => true,
                'est_actif' => true,
                'ordre_affichage' => 1
            ],
            [
                'code' => 'SALAIRES',
                'nom' => 'Salaires et charges sociales',
                'description' => 'Salaires du personnel et charges sociales',
                'type' => 'personnel',
                'budget_annuel' => 500000.00,
                'seuil_alerte' => 400000.00,
                'budget_renouvelable' => true,
                'validation_obligatoire' => true,
                'est_actif' => true,
                'ordre_affichage' => 2
            ]
        ];

        foreach ($categories as $categorie) {
            DB::table('categories_depenses')->updateOrInsert(
                ['code' => $categorie['code']],
                $categorie
            );
        }
    }

    private function seedDepenses(): void
    {
        $categorieId = DB::table('categories_depenses')->where('code', 'MATERIEL_PEDAGOGIQUE')->value('id');
        $anneeId = DB::table('annees_scolaires')->where('code', '2024-2025')->value('id');
        $personnelId = DB::table('personnels')->first()?->id;

        if ($categorieId && $anneeId && $personnelId) {
            $depenses = [
                [
                    'categorie_depense_id' => $categorieId,
                    'annee_scolaire_id' => $anneeId,
                    'numero_depense' => 'DEP-2024-001',
                    'titre' => 'Achat de livres de français CP',
                    'description' => 'Achat de 50 exemplaires de livres de français pour les classes de CP',
                    'type_depense' => 'achat',
                    'statut' => 'approuvee',
                    'montant_ht' => 2500.00,
                    'montant_tva' => 500.00,
                    'montant_ttc' => 3000.00,
                    'montant_paye' => 3000.00,
                    'montant_restant' => 0.00,
                    'date_demande' => '2024-09-15',
                    'date_approbation' => '2024-09-16',
                    'date_realisation' => '2024-09-20',
                    'date_paiement' => '2024-09-25',
                    'fournisseur' => 'Librairie Éducative Plus',
                    'numero_facture_fournisseur' => 'FACT-2024-089',
                    'urgence' => 'normale',
                    'priorite' => 'moyenne',
                    'demande_par_id' => $personnelId,
                    'approuve_par_id' => $personnelId,
                    'valide_par_id' => $personnelId,
                    'paye_par_id' => $personnelId,
                    'justification_demande' => 'Nouveaux programmes scolaires, besoin de nouveaux manuels',
                    'notification_envoyee' => true
                ]
            ];

            foreach ($depenses as $depense) {
                DB::table('depenses')->updateOrInsert(
                    ['numero_depense' => $depense['numero_depense']],
                    $depense
                );
            }
        }
    }

    private function seedAbsencesEleves(): void
    {
        $eleveId = DB::table('eleves')->first()?->id;
        $classeId = DB::table('classes')->first()?->id;
        $matiereId = DB::table('matieres')->first()?->id;
        $enseignantId = DB::table('personnels')->first()?->id;
        $anneeId = DB::table('annees_scolaires')->where('code', '2024-2025')->value('id');

        if ($eleveId && $classeId && $anneeId) {
            $absences = [
                [
                    'eleve_id' => $eleveId,
                    'classe_id' => $classeId,
                    'matiere_id' => $matiereId,
                    'enseignant_id' => $enseignantId,
                    'annee_scolaire_id' => $anneeId,
                    'date_absence' => '2024-10-15',
                    'type_absence' => 'complete',
                    'statut' => 'justifiee',
                    'motif_absence' => 'Maladie',
                    'justification_parent' => 'Certificat médical fourni',
                    'date_justification' => '2024-10-16',
                    'declaree_par_id' => $enseignantId,
                    'validee_par_id' => $enseignantId,
                    'date_declaration' => '2024-10-15 08:30:00',
                    'date_validation' => '2024-10-16 09:00:00',
                    'notification_parent_envoyee' => true,
                    'sanction_appliquee' => false
                ]
            ];

            foreach ($absences as $absence) {
                DB::table('absences_eleves')->updateOrInsert(
                    [
                        'eleve_id' => $absence['eleve_id'],
                        'matiere_id' => $absence['matiere_id'],
                        'date_absence' => $absence['date_absence']
                    ],
                    $absence
                );
            }
        }
    }

    private function seedMessages(): void
    {
        $personnelId = DB::table('personnels')->first()?->id;

        if ($personnelId) {
            $messages = [
                [
                    'sujet' => 'Réunion parents-professeurs',
                    'contenu' => 'La réunion parents-professeurs aura lieu le vendredi 15 novembre à 18h00. Merci de confirmer votre présence.',
                    'type_message' => 'parent',
                    'priorite' => 'normale',
                    'statut' => 'envoye',
                    'expediteur_id' => $personnelId,
                    'destinataires_parents' => json_encode([1, 2, 3]),
                    'date_envoi' => '2024-10-25 10:00:00',
                    'notification_email_envoyee' => true,
                    'notification_sms_envoyee' => false,
                    'confidentiel' => false
                ]
            ];

            foreach ($messages as $message) {
                DB::table('messages')->updateOrInsert(
                    [
                        'sujet' => $message['sujet'],
                        'expediteur_id' => $message['expediteur_id'],
                        'date_envoi' => $message['date_envoi']
                    ],
                    $message
                );
            }
        }
    }

    private function seedAnnonces(): void
    {
        $personnelId = DB::table('personnels')->first()?->id;

        if ($personnelId) {
            $annonces = [
                [
                    'titre' => 'Journée portes ouvertes',
                    'contenu' => 'L\'école organise une journée portes ouvertes le samedi 30 novembre de 9h à 12h. Venez découvrir nos programmes franco-arabes !',
                    'type_annonce' => 'evenement',
                    'priorite' => 'normale',
                    'statut' => 'publiee',
                    'auteur_id' => $personnelId,
                    'valide_par_id' => $personnelId,
                    'date_debut' => '2024-11-30',
                    'date_fin' => '2024-11-30',
                    'heure_debut' => '09:00:00',
                    'heure_fin' => '12:00:00',
                    'lieu' => 'École Franco-Arabe',
                    'date_publication' => '2024-10-20 10:00:00',
                    'date_validation' => '2024-10-20 10:00:00',
                    'notification_email_envoyee' => true,
                    'afficher_portail_parents' => true,
                    'afficher_portail_eleves' => true,
                    'afficher_ecran_accueil' => true
                ]
            ];

            foreach ($annonces as $annonce) {
                DB::table('annonces')->updateOrInsert(
                    [
                        'titre' => $annonce['titre'],
                        'auteur_id' => $annonce['auteur_id'],
                        'date_publication' => $annonce['date_publication']
                    ],
                    $annonce
                );
            }
        }
    }
}
