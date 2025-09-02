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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('inscription_financiere_id')->constrained('inscriptions_financieres')->onDelete('cascade');
            $table->foreignId('mode_paiement_id')->constrained('modes_paiement')->onDelete('restrict');
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations du paiement
            $table->string('numero_paiement')->unique(); // Numéro unique du paiement
            $table->enum('type_paiement', ['acompte', 'echeance', 'reglement_complet', 'remboursement', 'autre'])->default('echeance');
            $table->enum('statut', ['en_attente', 'en_cours', 'valide', 'refuse', 'annule', 'rembourse'])->default('en_attente');
            $table->enum('statut_traitement', ['non_traite', 'en_traitement', 'traite', 'erreur'])->default('non_traite');
            
            // Montants
            $table->decimal('montant_demande', 10, 2); // Montant demandé
            $table->decimal('montant_paye', 10, 2)->default(0.00); // Montant effectivement payé
            $table->decimal('montant_restant', 10, 2)->default(0.00); // Montant restant
            $table->decimal('frais_transaction', 10, 2)->default(0.00); // Frais de transaction
            $table->decimal('montant_net', 10, 2)->default(0.00); // Montant net reçu
            
            // Gestion des échéances
            $table->integer('numero_echeance')->nullable(); // Numéro de l'échéance
            $table->date('date_echeance'); // Date d'échéance
            $table->date('date_paiement')->nullable(); // Date effective du paiement
            $table->date('date_limite_paiement')->nullable(); // Date limite de paiement
            
            // Informations de paiement
            $table->string('reference_paiement')->nullable(); // Référence du paiement (chèque, virement, etc.)
            $table->string('numero_transaction')->nullable(); // Numéro de transaction bancaire
            $table->string('numero_cheque')->nullable(); // Numéro de chèque
            $table->string('banque_emission')->nullable(); // Banque d'émission
            $table->string('compte_emetteur')->nullable(); // Compte émetteur
            
            // Gestion des erreurs et retours
            $table->text('erreur_paiement')->nullable(); // Erreur lors du paiement
            $table->text('motif_refus')->nullable(); // Motif du refus
            $table->text('commentaires_banque')->nullable(); // Commentaires de la banque
            $table->integer('nombre_tentatives')->default(0); // Nombre de tentatives
            
            // Gestion des remises et pénalités
            $table->decimal('montant_remise', 10, 2)->default(0.00); // Remise accordée
            $table->text('motif_remise')->nullable(); // Motif de la remise
            $table->decimal('montant_penalite', 10, 2)->default(0.00); // Pénalité de retard
            $table->text('motif_penalite')->nullable(); // Motif de la pénalité
            
            // Gestion administrative
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('annule_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_annulation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->text('commentaires_annulation')->nullable();
            
            // Gestion des documents
            $table->boolean('recu_genere')->default(false); // Reçu généré ou non
            $table->string('numero_recu')->nullable(); // Numéro du reçu
            $table->string('fichier_recu')->nullable(); // Fichier du reçu
            $table->boolean('facture_generee')->default(false); // Facture générée ou non
            $table->string('numero_facture')->nullable(); // Numéro de la facture
            
            // Métadonnées
            $table->json('details_paiement')->nullable(); // Détails du paiement (JSON)
            $table->json('historique_statuts')->nullable(); // Historique des changements de statut (JSON)
            $table->text('observations')->nullable(); // Observations générales
            $table->json('metadonnees_techniques')->nullable(); // Métadonnées techniques (JSON)
            
            // Informations de sécurité
            $table->string('ip_paiement')->nullable(); // IP du paiement en ligne
            $table->string('user_agent')->nullable(); // User agent du navigateur
            $table->string('hash_securite')->nullable(); // Hash de sécurité
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['inscription_financiere_id', 'statut']);
            $table->index(['eleve_id', 'annee_scolaire_id']);
            $table->index(['mode_paiement_id', 'statut']);
            $table->index(['statut', 'statut_traitement']);
            $table->index('date_echeance');
            $table->index('date_paiement');
            $table->index('date_limite_paiement');
            $table->index('numero_echeance');
            $table->index('cree_par_id');
            $table->index('valide_par_id');
            $table->index('reference_paiement');
            $table->index('numero_transaction');
            $table->index('recu_genere');
            $table->index('facture_generee');
            
            // Contrainte unique : un paiement par échéance et inscription
            $table->unique(['inscription_financiere_id', 'numero_echeance'], 'uk_paiement_echeance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
