<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('inscription_financiere_id')->constrained('inscriptions_financieres')->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->foreignId('grille_tarifaire_id')->constrained('grilles_tarifaires')->onDelete('restrict');
            
            // Informations de la facture
            $table->string('numero_facture')->unique(); // Numéro unique de la facture
            $table->enum('type_facture', ['facture_initiale', 'facture_echeance', 'facture_ajustement', 'facture_remboursement'])->default('facture_initiale');
            $table->enum('statut', ['brouillon', 'emise', 'envoyee', 'payee', 'annulee', 'en_retard'])->default('brouillon');
            $table->enum('statut_paiement', ['non_payee', 'partiellement_payee', 'payee', 'en_retard'])->default('non_payee');
            
            // Dates importantes
            $table->date('date_emission'); // Date d'émission de la facture
            $table->date('date_echeance'); // Date d'échéance de paiement
            $table->date('date_paiement_complet')->nullable(); // Date de paiement complet
            $table->date('date_envoi')->nullable(); // Date d'envoi au client
            
            // Montants
            $table->decimal('montant_ht', 10, 2)->default(0.00); // Montant HT
            $table->decimal('montant_tva', 10, 2)->default(0.00); // Montant de la TVA
            $table->decimal('montant_ttc', 10, 2)->default(0.00); // Montant TTC
            $table->decimal('montant_remise', 10, 2)->default(0.00); // Montant des remises
            $table->decimal('montant_net_a_payer', 10, 2)->default(0.00); // Montant net à payer
            $table->decimal('montant_paye', 10, 2)->default(0.00); // Montant déjà payé
            $table->decimal('montant_restant', 10, 2)->default(0.00); // Montant restant à payer
            
            // Gestion de la TVA
            $table->decimal('taux_tva', 5, 2)->default(0.00); // Taux de TVA appliqué
            $table->boolean('tva_applicable')->default(false); // TVA applicable ou non
            $table->string('numero_tva')->nullable(); // Numéro de TVA de l'établissement
            
            // Gestion des échéances
            $table->integer('numero_echeance')->nullable(); // Numéro de l'échéance
            $table->enum('mode_echeances', ['comptant', 'echeances', 'mensuel', 'trimestriel', 'semestriel'])->default('comptant');
            $table->integer('nombre_echeances_total')->default(1); // Nombre total d'échéances
            $table->integer('echeance_courante')->default(1); // Échéance courante
            
            // Informations client
            $table->string('nom_client'); // Nom du client (parent/élève)
            $table->string('adresse_client')->nullable(); // Adresse du client
            $table->string('telephone_client')->nullable(); // Téléphone du client
            $table->string('email_client')->nullable(); // Email du client
            
            // Informations de l'établissement
            $table->string('nom_etablissement'); // Nom de l'établissement
            $table->string('adresse_etablissement')->nullable(); // Adresse de l'établissement
            $table->string('telephone_etablissement')->nullable(); // Téléphone de l'établissement
            $table->string('email_etablissement')->nullable(); // Email de l'établissement
            $table->string('siret_etablissement')->nullable(); // Numéro SIRET
            $table->string('ape_etablissement')->nullable(); // Code APE
            
            // Gestion des documents
            $table->string('fichier_facture')->nullable(); // Fichier PDF de la facture
            $table->string('hash_fichier')->nullable(); // Hash pour vérifier l'intégrité
            $table->integer('taille_fichier')->nullable(); // Taille du fichier en octets
            $table->boolean('facture_envoyee_email')->default(false); // Facture envoyée par email
            $table->boolean('facture_envoyee_postale')->default(false); // Facture envoyée par courrier
            
            // Gestion administrative
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('annule_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_annulation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->text('commentaires_annulation')->nullable();
            $table->text('notes_internes')->nullable();
            
            // Gestion des relances
            $table->integer('nombre_relances')->default(0); // Nombre de relances envoyées
            $table->date('date_derniere_relance')->nullable(); // Date de la dernière relance
            $table->text('historique_relances')->nullable(); // Historique des relances
            
            // Métadonnées
            $table->json('details_frais')->nullable(); // Détail des frais facturés (JSON)
            $table->json('historique_modifications')->nullable(); // Historique des modifications (JSON)
            $table->json('metadonnees_generation')->nullable(); // Métadonnées de génération (JSON)
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['inscription_financiere_id', 'statut']);
            $table->index(['eleve_id', 'annee_scolaire_id']);
            $table->index(['grille_tarifaire_id', 'type_facture']);
            $table->index(['statut', 'statut_paiement']);
            $table->index('date_emission');
            $table->index('date_echeance');
            $table->index('date_paiement_complet');
            $table->index('numero_echeance');
            $table->index('cree_par_id');
            $table->index('valide_par_id');
            $table->index('annule_par_id');
            $table->index('tva_applicable');
            $table->index('facture_envoyee_email');
            $table->index('facture_envoyee_postale');
            
            // Contrainte unique : une facture par échéance et inscription
            $table->unique(['inscription_financiere_id', 'numero_echeance'], 'uk_facture_echeance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
