<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module7Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du seeding du Module 7 : Documents & Génération...');
        
        // 1. Créer les types de documents
        $this->createTypesDocuments();
        
        // 2. Créer les variables de templates
        $this->createVariablesTemplates();
        
        // 3. Créer les templates de documents
        $this->createTemplatesDocuments();
        
        // 4. Créer des documents générés d'exemple
        $this->createDocumentsGeneres();
        
        $this->command->info('✅ Module 7 : Documents & Génération - Seeding terminé !');
    }
    
    private function createTypesDocuments(): void
    {
        $this->command->info('📄 Création des types de documents...');
        
        $typesDocuments = [
            // Documents pédagogiques
            [
                'code' => 'BULLETIN',
                'nom' => 'Bulletin de notes',
                'description' => 'Bulletin trimestriel ou semestriel avec les notes et appréciations',
                'categorie' => 'pedagogique',
                'contexte' => 'eleve',
                'est_obligatoire' => true,
                'est_visible_parents' => true,
                'est_periodique' => true,
                'frequence_generation' => 'trimestre',
                'periodes_autorisees' => json_encode(['trimestre', 'semestre']),
                'format_sortie' => 'pdf',
                'est_editable' => false,
                'est_archivable' => true,
                'duree_conservation_mois' => 120,
                'version_template' => '1.0',
                'est_version_active' => true,
                'champs_obligatoires' => json_encode(['eleve_nom', 'eleve_prenom', 'classe_nom', 'periode_nom', 'notes_matiere']),
                'champs_optionnels' => json_encode(['appreciation_generale', 'conseils_orientation']),
                'conditions_generation' => 'Notes validées pour la période',
                'notes_techniques' => 'Généré automatiquement à la fin de chaque période',
            ],
            [
                'code' => 'CERTIFICAT_SCOLARITE',
                'nom' => 'Certificat de scolarité',
                'description' => 'Attestation de présence et d\'inscription de l\'élève',
                'categorie' => 'administratif',
                'contexte' => 'eleve',
                'est_obligatoire' => true,
                'est_visible_parents' => true,
                'est_periodique' => false,
                'frequence_generation' => 'sur_demande',
                'periodes_autorisees' => null,
                'format_sortie' => 'pdf',
                'est_editable' => false,
                'est_archivable' => true,
                'duree_conservation_mois' => 60,
                'version_template' => '1.0',
                'est_version_active' => true,
                'champs_obligatoires' => json_encode(['eleve_nom', 'eleve_prenom', 'classe_nom', 'annee_scolaire', 'date_emission']),
                'champs_optionnels' => json_encode(['directeur_signature', 'cachet_ecole']),
                'conditions_generation' => 'Élève inscrit et actif',
                'notes_techniques' => 'Généré sur demande pour les administrations',
            ],
            [
                'code' => 'CERTIFICAT_TRANSFERT',
                'nom' => 'Certificat de transfert',
                'description' => 'Document officiel pour le transfert vers un autre établissement',
                'categorie' => 'administratif',
                'contexte' => 'eleve',
                'est_obligatoire' => false,
                'est_visible_parents' => true,
                'est_periodique' => false,
                'frequence_generation' => 'sur_demande',
                'periodes_autorisees' => null,
                'format_sortie' => 'pdf',
                'est_editable' => false,
                'est_archivable' => true,
                'duree_conservation_mois' => 120,
                'version_template' => '1.0',
                'est_version_active' => true,
                'champs_obligatoires' => json_encode(['eleve_nom', 'eleve_prenom', 'classe_nom', 'niveau_nom', 'date_depart', 'motif_transfert']),
                'champs_optionnels' => json_encode(['appreciation_directeur', 'recommandations']),
                'conditions_generation' => 'Demande de transfert validée',
                'notes_techniques' => 'Généré lors des demandes de transfert',
            ],
            [
                'code' => 'LISTE_CLASSE',
                'nom' => 'Liste de classe',
                'description' => 'Liste des élèves d\'une classe avec informations de base',
                'categorie' => 'administratif',
                'contexte' => 'classe',
                'est_obligatoire' => false,
                'est_visible_parents' => false,
                'est_periodique' => false,
                'frequence_generation' => 'sur_demande',
                'periodes_autorisees' => null,
                'format_sortie' => 'pdf',
                'est_editable' => false,
                'est_archivable' => true,
                'duree_conservation_mois' => 24,
                'version_template' => '1.0',
                'est_version_active' => true,
                'champs_obligatoires' => json_encode(['classe_nom', 'niveau_nom', 'programme_nom', 'eleves_liste']),
                'champs_optionnels' => json_encode(['enseignant_titulaire', 'effectif_total']),
                'conditions_generation' => 'Classe active avec élèves inscrits',
                'notes_techniques' => 'Utilisé par les enseignants et l\'administration',
            ],
            [
                'code' => 'RAPPORT_PRESENCE',
                'nom' => 'Rapport de présence',
                'description' => 'Rapport des présences et absences par classe et période',
                'categorie' => 'statistique',
                'contexte' => 'classe',
                'est_obligatoire' => false,
                'est_visible_parents' => false,
                'est_periodique' => true,
                'frequence_generation' => 'trimestre',
                'periodes_autorisees' => json_encode(['trimestre', 'semestre']),
                'format_sortie' => 'pdf',
                'est_editable' => false,
                'est_archivable' => true,
                'duree_conservation_mois' => 60,
                'version_template' => '1.0',
                'est_version_active' => true,
                'champs_obligatoires' => json_encode(['classe_nom', 'periode_nom', 'eleves_presence', 'statistiques_presence']),
                'champs_optionnels' => json_encode(['analyse_tendances', 'recommandations']),
                'conditions_generation' => 'Données de présence disponibles',
                'notes_techniques' => 'Généré pour l\'analyse des taux de présence',
            ],
        ];
        
        foreach ($typesDocuments as $type) {
            DB::table('types_documents')->insert($type);
        }
        
        $this->command->info('✅ ' . count($typesDocuments) . ' types de documents créés');
    }
    
    private function createVariablesTemplates(): void
    {
        $this->command->info('🔤 Création des variables de templates...');
        
        $variables = [
            // Variables élèves
            [
                'code' => 'ELEVE_NOM',
                'nom' => 'Nom de l\'élève',
                'description' => 'Nom de famille de l\'élève',
                'type' => 'texte',
                'categorie' => 'eleve',
                'types_documents_compatibles' => json_encode(['BULLETIN', 'CERTIFICAT_SCOLARITE', 'CERTIFICAT_TRANSFERT']),
                'contextes_utilisation' => json_encode(['eleve']),
                'est_obligatoire' => true,
                'valeur_defaut' => '',
                'format_affichage' => 'majuscules',
                'longueur_min' => 2,
                'longueur_max' => 50,
                'est_critique' => true,
                'exemple_utilisation' => '{{ELEVE_NOM}}',
                'source_donnees' => 'eleves',
                'champ_source' => 'nom_famille',
                'logique_calcul' => 'Valeur directe du champ',
            ],
            [
                'code' => 'ELEVE_PRENOM',
                'nom' => 'Prénom de l\'élève',
                'description' => 'Prénom de l\'élève',
                'type' => 'texte',
                'categorie' => 'eleve',
                'types_documents_compatibles' => json_encode(['BULLETIN', 'CERTIFICAT_SCOLARITE', 'CERTIFICAT_TRANSFERT']),
                'contextes_utilisation' => json_encode(['eleve']),
                'est_obligatoire' => true,
                'valeur_defaut' => '',
                'format_affichage' => 'capitalize',
                'longueur_min' => 2,
                'longueur_max' => 50,
                'est_critique' => true,
                'exemple_utilisation' => '{{ELEVE_PRENOM}}',
                'source_donnees' => 'eleves',
                'champ_source' => 'prenom',
                'logique_calcul' => 'Valeur directe du champ',
            ],
            [
                'code' => 'CLASSE_NOM',
                'nom' => 'Nom de la classe',
                'description' => 'Nom de la classe de l\'élève',
                'type' => 'texte',
                'categorie' => 'classe',
                'types_documents_compatibles' => json_encode(['BULLETIN', 'CERTIFICAT_SCOLARITE', 'CERTIFICAT_TRANSFERT', 'LISTE_CLASSE']),
                'contextes_utilisation' => json_encode(['eleve', 'classe']),
                'est_obligatoire' => true,
                'valeur_defaut' => '',
                'format_affichage' => 'majuscules',
                'longueur_min' => 2,
                'longueur_max' => 30,
                'est_critique' => true,
                'exemple_utilisation' => '{{CLASSE_NOM}}',
                'source_donnees' => 'classes',
                'champ_source' => 'nom',
                'logique_calcul' => 'Valeur directe du champ',
            ],
            [
                'code' => 'MOYENNE_GENERALE_FR',
                'nom' => 'Moyenne générale Français',
                'description' => 'Moyenne générale du programme français',
                'type' => 'nombre',
                'categorie' => 'note',
                'types_documents_compatibles' => json_encode(['BULLETIN']),
                'contextes_utilisation' => json_encode(['eleve']),
                'est_obligatoire' => true,
                'valeur_defaut' => '0.00',
                'format_affichage' => '##.##',
                'unite' => 'points',
                'valeur_min' => 0.00,
                'valeur_max' => 20.00,
                'est_critique' => false,
                'exemple_utilisation' => '{{MOYENNE_GENERALE_FR}}/20',
                'source_donnees' => 'bulletins_eleves',
                'champ_source' => 'moyenne_generale_francais',
                'logique_calcul' => 'Calculée à partir des moyennes par matière',
            ],
            [
                'code' => 'MOYENNE_GENERALE_AR',
                'nom' => 'Moyenne générale Arabe',
                'description' => 'Moyenne générale du programme arabe',
                'type' => 'nombre',
                'categorie' => 'note',
                'types_documents_compatibles' => json_encode(['BULLETIN']),
                'contextes_utilisation' => json_encode(['eleve']),
                'est_obligatoire' => true,
                'valeur_defaut' => '0.00',
                'format_affichage' => '##.##',
                'unite' => 'points',
                'valeur_min' => 0.00,
                'valeur_max' => 20.00,
                'est_critique' => false,
                'exemple_utilisation' => '{{MOYENNE_GENERALE_AR}}/20',
                'source_donnees' => 'bulletins_eleves',
                'champ_source' => 'moyenne_generale_arabe',
                'logique_calcul' => 'Calculée à partir des moyennes par matière',
            ],
            [
                'code' => 'PERIODE_NOM',
                'nom' => 'Nom de la période',
                'description' => 'Nom de la période scolaire (trimestre, semestre)',
                'type' => 'texte',
                'categorie' => 'periode',
                'types_documents_compatibles' => json_encode(['BULLETIN', 'RAPPORT_PRESENCE']),
                'contextes_utilisation' => json_encode(['eleve', 'classe', 'periode']),
                'est_obligatoire' => true,
                'valeur_defaut' => '',
                'format_affichage' => 'capitalize',
                'longueur_min' => 3,
                'longueur_max' => 30,
                'est_critique' => true,
                'exemple_utilisation' => '{{PERIODE_NOM}}',
                'source_donnees' => 'periodes',
                'champ_source' => 'nom',
                'logique_calcul' => 'Valeur directe du champ',
            ],
            [
                'code' => 'ANNEE_SCOLAIRE',
                'nom' => 'Année scolaire',
                'description' => 'Année scolaire en cours',
                'type' => 'texte',
                'categorie' => 'annee',
                'types_documents_compatibles' => json_encode(['BULLETIN', 'CERTIFICAT_SCOLARITE', 'CERTIFICAT_TRANSFERT', 'LISTE_CLASSE']),
                'contextes_utilisation' => json_encode(['eleve', 'classe', 'annee']),
                'est_obligatoire' => true,
                'valeur_defaut' => '',
                'format_affichage' => 'YYYY-YYYY',
                'longueur_min' => 9,
                'longueur_max' => 9,
                'est_critique' => true,
                'exemple_utilisation' => '{{ANNEE_SCOLAIRE}}',
                'source_donnees' => 'annees_scolaires',
                'champ_source' => 'annee_debut',
                'logique_calcul' => 'Format: annee_debut-annee_fin',
            ],
        ];
        
        foreach ($variables as $variable) {
            DB::table('variables_templates')->insert($variable);
        }
        
        $this->command->info('✅ ' . count($variables) . ' variables de templates créées');
    }
    
    private function createTemplatesDocuments(): void
    {
        $this->command->info('📋 Création des templates de documents...');
        
        $templates = [
            // Template Bulletin Français
            [
                'type_document_id' => 1, // BULLETIN
                'programme_id' => 1, // Français
                'code' => 'TEMPL_BULLETIN_FR',
                'nom' => 'Template Bulletin Français',
                'description' => 'Template pour les bulletins du programme français',
                'version' => '1.0',
                'statut' => 'actif',
                'est_template_par_defaut' => true,
                'fichier_template' => 'templates/bulletins/bulletin_francais.html',
                'fichier_css' => 'templates/bulletins/bulletin_francais.css',
                'fichier_preview' => 'templates/bulletins/preview_bulletin_fr.png',
                'variables_disponibles' => json_encode(['ELEVE_NOM', 'ELEVE_PRENOM', 'CLASSE_NOM', 'MOYENNE_GENERALE_FR', 'PERIODE_NOM', 'ANNEE_SCOLAIRE']),
                'sections_template' => json_encode(['en_tete', 'informations_eleve', 'notes_matiere', 'moyennes', 'appreciations', 'signatures']),
                'styles_disponibles' => json_encode(['moderne', 'classique', 'compact']),
                'parametres_generation' => json_encode(['format' => 'A4', 'orientation' => 'portrait', 'marges' => '2cm']),
                'langue_principale' => 'fr',
                'langues_supportees' => json_encode(['fr']),
                'est_multilingue' => false,
                'est_visible_enseignants' => true,
                'est_visible_administration' => true,
                'est_visible_parents' => false,
                'est_visible_eleves' => false,
                'instructions_utilisation' => 'Template standard pour les bulletins français',
                'notes_techniques' => 'Utilise les variables standard du système',
                'cree_par_id' => 1,
                'valide_par_id' => 1,
                'date_validation' => now(),
            ],
            // Template Bulletin Arabe
            [
                'type_document_id' => 1, // BULLETIN
                'programme_id' => 2, // Arabe
                'code' => 'TEMPL_BULLETIN_AR',
                'nom' => 'Template Bulletin Arabe',
                'description' => 'Template pour les bulletins du programme arabe',
                'version' => '1.0',
                'statut' => 'actif',
                'est_template_par_defaut' => true,
                'fichier_template' => 'templates/bulletins/bulletin_arabe.html',
                'fichier_css' => 'templates/bulletins/bulletin_arabe.css',
                'fichier_preview' => 'templates/bulletins/preview_bulletin_ar.png',
                'variables_disponibles' => json_encode(['ELEVE_NOM', 'ELEVE_PRENOM', 'CLASSE_NOM', 'MOYENNE_GENERALE_AR', 'PERIODE_NOM', 'ANNEE_SCOLAIRE']),
                'sections_template' => json_encode(['en_tete', 'informations_eleve', 'notes_matiere', 'moyennes', 'appreciations', 'signatures']),
                'styles_disponibles' => json_encode(['moderne', 'classique', 'compact']),
                'parametres_generation' => json_encode(['format' => 'A4', 'orientation' => 'portrait', 'marges' => '2cm']),
                'langue_principale' => 'ar',
                'langues_supportees' => json_encode(['ar', 'fr']),
                'est_multilingue' => true,
                'est_visible_enseignants' => true,
                'est_visible_administration' => true,
                'est_visible_parents' => false,
                'est_visible_eleves' => false,
                'instructions_utilisation' => 'Template bilingue pour les bulletins arabes',
                'notes_techniques' => 'Support bilingue arabe-français',
                'cree_par_id' => 2,
                'valide_par_id' => 1,
                'date_validation' => now(),
            ],
            // Template Certificat de scolarité
            [
                'type_document_id' => 2, // CERTIFICAT_SCOLARITE
                'programme_id' => null, // Tous programmes
                'code' => 'TEMPL_CERTIFICAT_SCOLARITE',
                'nom' => 'Template Certificat de scolarité',
                'description' => 'Template standard pour les certificats de scolarité',
                'version' => '1.0',
                'statut' => 'actif',
                'est_template_par_defaut' => true,
                'fichier_template' => 'templates/certificats/certificat_scolarite.html',
                'fichier_css' => 'templates/certificats/certificat_scolarite.css',
                'fichier_preview' => 'templates/certificats/preview_certificat.png',
                'variables_disponibles' => json_encode(['ELEVE_NOM', 'ELEVE_PRENOM', 'CLASSE_NOM', 'ANNEE_SCOLAIRE', 'DATE_EMISSION']),
                'sections_template' => json_encode(['en_tete_ecole', 'informations_eleve', 'attestation_scolarite', 'signatures']),
                'styles_disponibles' => json_encode(['officiel', 'moderne']),
                'parametres_generation' => json_encode(['format' => 'A4', 'orientation' => 'portrait', 'marges' => '2.5cm']),
                'langue_principale' => 'fr',
                'langues_supportees' => json_encode(['fr']),
                'est_multilingue' => false,
                'est_visible_enseignants' => false,
                'est_visible_administration' => true,
                'est_visible_parents' => false,
                'est_visible_eleves' => false,
                'instructions_utilisation' => 'Template officiel pour les certificats',
                'notes_techniques' => 'Format officiel avec signature et cachet',
                'cree_par_id' => 1,
                'valide_par_id' => 1,
                'date_validation' => now(),
            ],
        ];
        
        foreach ($templates as $template) {
            DB::table('templates_documents')->insert($template);
        }
        
        $this->command->info('✅ ' . count($templates) . ' templates de documents créés');
    }
    
    private function createDocumentsGeneres(): void
    {
        $this->command->info('📄 Création des documents générés...');
        
        $documents = [
            // Bulletin Français - Élève 1
            [
                'type_document_id' => 1, // BULLETIN
                'template_document_id' => 1, // Template Bulletin Français
                'annee_scolaire_id' => 1,
                'eleve_id' => 1,
                'classe_id' => 1, // CP-A Français
                'periode_id' => 1, // 1er Trimestre
                'numero_document' => 'DOC-2024-001-001',
                'titre_document' => 'Bulletin 1er Trimestre - CP-A Français - Élève 1',
                'description' => 'Bulletin du 1er trimestre pour l\'élève en CP-A Français',
                'statut' => 'publie',
                'priorite' => 'normale',
                'est_urgent' => false,
                'fichier_principal' => 'documents/bulletins/BUL-2024-001-001.pdf',
                'hash_fichier' => 'abc123def456ghi789',
                'taille_fichier' => 245760,
                'variables_utilisees' => json_encode(['ELEVE_NOM' => 'Dupont', 'ELEVE_PRENOM' => 'Jean', 'CLASSE_NOM' => 'CP-A Français']),
                'parametres_generation' => json_encode(['format' => 'A4', 'style' => 'moderne']),
                'metadonnees_document' => json_encode(['version_template' => '1.0', 'date_generation' => now()->toISOString()]),
                'version_document' => 1,
                'est_derniere_version' => true,
                'est_visible_enseignants' => true,
                'est_visible_administration' => true,
                'est_visible_parents' => true,
                'est_visible_eleves' => false,
                'demande_par_id' => 1,
                'genere_par_id' => 1,
                'valide_par_id' => 1,
                'publie_par_id' => 1,
                'date_demande' => now()->subDays(2),
                'date_debut_generation' => now()->subDays(1),
                'date_fin_generation' => now()->subDays(1),
                'date_validation' => now()->subDays(1),
                'date_publication' => now()->subDays(1),
                'commentaires_demande' => 'Génération automatique fin de trimestre',
                'commentaires_generation' => 'Généré avec succès',
                'commentaires_validation' => 'Validé par le directeur',
                'commentaires_publication' => 'Publié sur le portail parents',
                'nombre_consultations' => 3,
                'nombre_telechargements' => 1,
                'derniere_consultation' => now()->subHours(2),
                'dernier_telechargement' => now()->subHours(3),
            ],
            // Bulletin Arabe - Élève 1
            [
                'type_document_id' => 1, // BULLETIN
                'template_document_id' => 2, // Template Bulletin Arabe
                'annee_scolaire_id' => 1,
                'eleve_id' => 1,
                'classe_id' => 6, // CP-A Arabe
                'periode_id' => 1, // 1er Trimestre
                'numero_document' => 'DOC-2024-001-002',
                'titre_document' => 'Bulletin 1er Trimestre - CP-A Arabe - Élève 1',
                'description' => 'Bulletin du 1er trimestre pour l\'élève en CP-A Arabe',
                'statut' => 'publie',
                'priorite' => 'normale',
                'est_urgent' => false,
                'fichier_principal' => 'documents/bulletins/BUL-2024-001-002.pdf',
                'hash_fichier' => 'def456ghi789jkl012',
                'taille_fichier' => 238592,
                'variables_utilisees' => json_encode(['ELEVE_NOM' => 'Dupont', 'ELEVE_PRENOM' => 'Jean', 'CLASSE_NOM' => 'CP-A Arabe']),
                'parametres_generation' => json_encode(['format' => 'A4', 'style' => 'moderne']),
                'metadonnees_document' => json_encode(['version_template' => '1.0', 'date_generation' => now()->toISOString()]),
                'version_document' => 1,
                'est_derniere_version' => true,
                'est_visible_enseignants' => true,
                'est_visible_administration' => true,
                'est_visible_parents' => true,
                'est_visible_eleves' => false,
                'demande_par_id' => 2,
                'genere_par_id' => 2,
                'valide_par_id' => 1,
                'publie_par_id' => 1,
                'date_demande' => now()->subDays(2),
                'date_debut_generation' => now()->subDays(1),
                'date_fin_generation' => now()->subDays(1),
                'date_validation' => now()->subDays(1),
                'date_publication' => now()->subDays(1),
                'commentaires_demande' => 'Génération automatique fin de trimestre',
                'commentaires_generation' => 'Généré avec succès',
                'commentaires_validation' => 'Validé par le directeur',
                'commentaires_publication' => 'Publié sur le portail parents',
                'nombre_consultations' => 2,
                'nombre_telechargements' => 1,
                'derniere_consultation' => now()->subHours(4),
                'dernier_telechargement' => now()->subHours(5),
            ],
        ];
        
        foreach ($documents as $document) {
            DB::table('documents_generes')->insert($document);
        }
        
        $this->command->info('✅ ' . count($documents) . ' documents générés créés');
    }
}
