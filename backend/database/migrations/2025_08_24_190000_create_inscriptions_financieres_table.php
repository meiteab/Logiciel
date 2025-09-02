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
        Schema::create('inscriptions_financieres', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->foreignId('grille_tarifaire_id')->constrained('grilles_tarifaires')->onDelete('restrict');
            
            // Informations de l'inscription
            $table->string('numero_inscription')->unique(); // Numéro unique de l'inscription
            $table->date('date_inscription'); // Date d'inscription
            $table->enum('statut', ['en_attente', 'validee', 'annulee', 'terminee'])->default('en_attente');
            $table->enum('type_inscription', ['nouvelle', 'reinscription', 'transfert'])->default('nouvelle');
            
            // Gestion des programmes
            $table->boolean('inscription_francais')->default(true); // Inscription au programme français
            $table->boolean('inscription_arabe')->default(true); // Inscription au programme arabe
            $table->foreignId('classe_francaise_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('classe_arabe_id')->nullable()->constrained('classes')->onDelete('set null');
            
            // Montants et calculs
            $table->decimal('montant_total_ht', 10, 2)->default(0.00); // Montant total HT
            $table->decimal('montant_tva', 10, 2)->default(0.00); // Montant de la TVA
            $table->decimal('montant_total_ttc', 10, 2)->default(0.00); // Montant total TTC
            $table->decimal('montant_remise', 10, 2)->default(0.00); // Montant des remises
            $table->decimal('montant_net_a_payer', 10, 2)->default(0.00); // Montant net à payer
            
            // Gestion des échéances
            $table->enum('mode_echeances', ['comptant', 'echeances', 'mensuel', 'trimestriel', 'semestriel'])->default('comptant');
            $table->integer('nombre_echeances')->default(1); // Nombre d'échéances
            $table->decimal('montant_echeance', 10, 2)->nullable(); // Montant par échéance
            $table->date('date_premiere_echeance')->nullable(); // Date de la première échéance
            
            // Gestion des remises et bourses
            $table->decimal('pourcentage_remise', 5, 2)->default(0.00); // Pourcentage de remise
            $table->text('motif_remise')->nullable(); // Motif de la remise
            $table->boolean('bourse_accordee')->default(false); // Bourse accordée ou non
            $table->decimal('montant_bourse', 10, 2)->default(0.00); // Montant de la bourse
            $table->text('motif_bourse')->nullable(); // Motif de la bourse
            
            // Gestion des documents
            $table->boolean('facture_generee')->default(false); // Facture générée ou non
            $table->string('numero_facture')->nullable(); // Numéro de la facture
            $table->date('date_facture')->nullable(); // Date de la facture
            $table->boolean('recu_genere')->default(false); // Reçu généré ou non
            
            // Informations administratives
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->text('notes_administratives')->nullable();
            
            // Gestion des paiements
            $table->decimal('montant_paye', 10, 2)->default(0.00); // Montant déjà payé
            $table->decimal('montant_restant', 10, 2)->default(0.00); // Montant restant à payer
            $table->enum('statut_paiement', ['en_attente', 'partiel', 'complet', 'en_retard'])->default('en_attente');
            $table->date('date_dernier_paiement')->nullable();
            $table->date('date_echeance_paiement')->nullable();
            
            // Métadonnées
            $table->json('details_frais')->nullable(); // Détail des frais par type (JSON)
            $table->json('historique_modifications')->nullable(); // Historique des modifications (JSON)
            $table->text('observations')->nullable(); // Observations générales
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'annee_scolaire_id']);
            $table->index(['grille_tarifaire_id', 'statut']);
            $table->index(['statut', 'statut_paiement']);
            $table->index('date_inscription');
            $table->index('date_echeance_paiement');
            $table->index('cree_par_id');
            $table->index('valide_par_id');
            $table->index('bourse_accordee');
            $table->index('facture_generee');
            
            // Contrainte unique : une inscription financière par élève et année
            $table->unique(['eleve_id', 'annee_scolaire_id'], 'uk_inscription_financiere_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions_financieres');
    }
};
