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
        Schema::create('dossiers_disciplinaires', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('set null');
            
            // Informations de l'incident
            $table->date('date_incident');
            $table->time('heure_incident')->nullable();
            $table->string('lieu_incident')->nullable();
            $table->enum('gravite', ['legere', 'moderee', 'grave', 'tres_grave'])->default('legere');
            $table->enum('statut', ['en_cours', 'resolu', 'escalade', 'archived'])->default('en_cours');
            
            // Description de l'incident
            $table->text('description_incident');
            $table->text('temoignages')->nullable(); // Témoignages des autres élèves/personnel
            $table->text('explications_eleve')->nullable(); // Explications de l'élève
            $table->text('explications_parents')->nullable(); // Explications des parents
            
            // Sanctions appliquées
            $table->enum('type_sanction', ['avertissement', 'blame', 'exclusion_temporaire', 'exclusion_definitive', 'autre'])->nullable();
            $table->text('details_sanction')->nullable();
            $table->date('date_debut_sanction')->nullable();
            $table->date('date_fin_sanction')->nullable();
            $table->integer('duree_sanction_jours')->nullable(); // Durée en jours si applicable
            
            // Suivi administratif
            $table->foreignId('signale_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('traite_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_signalement')->nullable();
            $table->timestamp('date_traitement')->nullable();
            $table->timestamp('date_validation')->nullable();
            
            // Notifications
            $table->boolean('notification_parents_envoyee')->default(false);
            $table->timestamp('date_notification_parents')->nullable();
            $table->text('commentaires_notification')->nullable();
            
            // Mesures éducatives
            $table->text('mesures_educatives')->nullable(); // Mesures éducatives proposées
            $table->text('suivi_psychologique')->nullable(); // Suivi psychologique si nécessaire
            $table->text('implication_parents')->nullable(); // Implication des parents
            
            // Résolution
            $table->text('actions_correctives')->nullable(); // Actions correctives mises en place
            $table->text('evaluation_suivi')->nullable(); // Évaluation du suivi
            $table->date('date_resolution')->nullable();
            $table->text('commentaires_resolution')->nullable();
            
            // Documents
            $table->json('documents_associes')->nullable(); // Documents associés (rapports, photos, etc.)
            $table->text('notes_confidentielles')->nullable(); // Notes confidentielles
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'annee_scolaire_id']);
            $table->index(['eleve_id', 'date_incident']);
            $table->index(['classe_id', 'date_incident']);
            $table->index('gravite');
            $table->index('statut');
            $table->index('type_sanction');
            $table->index('date_incident');
            $table->index('notification_parents_envoyee');
            $table->index('signale_par_id');
            $table->index('traite_par_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers_disciplinaires');
    }
};
