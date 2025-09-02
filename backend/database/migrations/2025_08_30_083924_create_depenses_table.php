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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('categorie_depense_id')->constrained('categories_depenses')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations de la dépense
            $table->string('numero_depense')->unique(); // Numéro unique de la dépense
            $table->string('titre'); // Titre de la dépense
            $table->text('description')->nullable(); // Description détaillée
            $table->enum('type_depense', ['achat', 'service', 'maintenance', 'formation', 'autre'])->default('achat');
            $table->enum('statut', ['proposee', 'approuvee', 'en_cours', 'terminee', 'annulee'])->default('proposee');
            
            // Montants
            $table->decimal('montant_ht', 12, 2); // Montant hors taxes
            $table->decimal('montant_tva', 12, 2)->default(0.00); // Montant de la TVA
            $table->decimal('montant_ttc', 12, 2); // Montant toutes taxes comprises
            $table->decimal('montant_paye', 12, 2)->default(0.00); // Montant déjà payé
            $table->decimal('montant_restant', 12, 2)->default(0.00); // Montant restant à payer
            
            // Dates
            $table->date('date_demande'); // Date de demande
            $table->date('date_approbation')->nullable(); // Date d'approbation
            $table->date('date_realisation')->nullable(); // Date de réalisation
            $table->date('date_paiement')->nullable(); // Date de paiement
            $table->date('date_echeance')->nullable(); // Date d'échéance de paiement
            
            // Fournisseur
            $table->string('fournisseur')->nullable(); // Nom du fournisseur
            $table->string('numero_facture_fournisseur')->nullable(); // Numéro de facture fournisseur
            $table->string('reference_commande')->nullable(); // Référence de commande
            
            // Gestion administrative
            $table->foreignId('demande_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('approuve_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('paye_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            
            // Commentaires et justifications
            $table->text('justification_demande')->nullable(); // Justification de la demande
            $table->text('commentaires_approbation')->nullable(); // Commentaires lors de l'approbation
            $table->text('commentaires_validation')->nullable(); // Commentaires lors de la validation
            $table->text('commentaires_paiement')->nullable(); // Commentaires lors du paiement
            
            // Documents
            $table->string('devis_fichier')->nullable(); // Fichier devis
            $table->string('facture_fichier')->nullable(); // Fichier facture
            $table->string('bon_livraison_fichier')->nullable(); // Fichier bon de livraison
            $table->json('autres_documents')->nullable(); // Autres documents associés
            
            // Urgence et priorité
            $table->enum('urgence', ['normale', 'urgente', 'tres_urgente'])->default('normale');
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'critique'])->default('moyenne');
            
            // Suivi
            $table->text('notes_suivi')->nullable(); // Notes de suivi
            $table->boolean('notification_envoyee')->default(false); // Notification envoyée
            $table->timestamp('date_notification')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['categorie_depense_id', 'annee_scolaire_id']);
            $table->index(['categorie_depense_id', 'statut']);
            $table->index(['annee_scolaire_id', 'date_demande']);
            $table->index('type_depense');
            $table->index('statut');
            $table->index('urgence');
            $table->index('priorite');
            $table->index('date_demande');
            $table->index('date_approbation');
            $table->index('date_realisation');
            $table->index('date_paiement');
            $table->index('demande_par_id');
            $table->index('approuve_par_id');
            $table->index('valide_par_id');
            $table->index('paye_par_id');
            $table->index('notification_envoyee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
