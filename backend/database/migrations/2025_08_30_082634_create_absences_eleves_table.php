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
        Schema::create('absences_eleves', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            $table->foreignId('enseignant_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations de l'absence
            $table->date('date_absence');
            $table->time('heure_debut')->nullable(); // Heure de début si absence partielle
            $table->time('heure_fin')->nullable(); // Heure de fin si absence partielle
            $table->enum('type_absence', ['complete', 'partielle', 'retard'])->default('complete');
            $table->enum('statut', ['justifiee', 'non_justifiee', 'en_attente'])->default('en_attente');
            
            // Justification
            $table->text('motif_absence')->nullable(); // Motif de l'absence
            $table->text('justification_parent')->nullable(); // Justification fournie par les parents
            $table->text('justification_administration')->nullable(); // Justification administrative
            $table->string('piece_justificative')->nullable(); // Fichier justificatif
            $table->date('date_justification')->nullable(); // Date de justification
            
            // Informations administratives
            $table->foreignId('declaree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('validee_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_declaration')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_declaration')->nullable();
            $table->text('commentaires_validation')->nullable();
            
            // Notifications
            $table->boolean('notification_parent_envoyee')->default(false);
            $table->timestamp('date_notification_parent')->nullable();
            $table->text('commentaires_notification')->nullable();
            
            // Suivi
            $table->boolean('sanction_appliquee')->default(false);
            $table->text('details_sanction')->nullable();
            $table->date('date_sanction')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'date_absence']);
            $table->index(['classe_id', 'date_absence']);
            $table->index(['matiere_id', 'date_absence']);
            $table->index(['enseignant_id', 'date_absence']);
            $table->index(['annee_scolaire_id', 'date_absence']);
            $table->index('type_absence');
            $table->index('statut');
            $table->index('date_absence');
            $table->index('notification_parent_envoyee');
            $table->index('sanction_appliquee');
            
            // Contrainte unique : un élève ne peut avoir qu'une absence par matière/jour
            $table->unique(['eleve_id', 'matiere_id', 'date_absence'], 'uk_absence_eleve_matiere_jour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences_eleves');
    }
};
