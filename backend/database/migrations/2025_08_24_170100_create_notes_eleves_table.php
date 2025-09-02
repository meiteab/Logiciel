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
        Schema::create('notes_eleves', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison principales
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('restrict');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // La note elle-même
            $table->decimal('note_obtenue', 5, 2)->nullable(); // Note sur 20 (ou autre échelle)
            $table->decimal('note_maximale', 5, 2)->default(20.00); // Note maximale de l'évaluation
            $table->decimal('note_ponderee', 5, 2)->nullable(); // Note × coefficient
            
            // Statut de la note
            $table->enum('statut', ['saisie', 'validee', 'modifiee', 'annulee'])->default('saisie');
            $table->boolean('est_absente')->default(false); // Élève absent
            $table->boolean('est_excuse')->default(false); // Absence justifiée
            $table->boolean('est_retard')->default(false); // Élève en retard
            
            // Informations pédagogiques
            $table->text('commentaires_enseignant')->nullable(); // Commentaires du prof
            $table->text('remarques_eleve')->nullable(); // Remarques de l'élève
            $table->text('observations_parents')->nullable(); // Observations des parents
            
            // Gestion administrative
            $table->foreignId('enseignant_id')->constrained('personnels')->onDelete('restrict');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_saisie')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_modification')->nullable();
            $table->text('motif_modification')->nullable();
            
            // Historique et traçabilité
            $table->json('historique_notes')->nullable(); // Historique des modifications
            $table->string('ip_saisie')->nullable(); // IP de saisie pour traçabilité
            $table->string('user_agent_saisie')->nullable(); // Navigateur utilisé
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'evaluation_id']);
            $table->index(['classe_id', 'matiere_id', 'periode_id']);
            $table->index(['programme_id', 'annee_scolaire_id']);
            $table->index('statut');
            $table->index('est_absente');
            $table->index('enseignant_id');
            $table->index('valide_par_id');
            $table->index('date_saisie');
            $table->index('date_validation');
            
            // Contrainte unique : un élève ne peut avoir qu'une note par évaluation
            $table->unique(['eleve_id', 'evaluation_id'], 'uk_eleve_evaluation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_eleves');
    }
};
