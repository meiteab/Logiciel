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
        Schema::create('presences_eleves', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('set null');
            $table->foreignId('enseignant_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations de présence
            $table->date('date_presence');
            $table->time('heure_debut')->nullable(); // Heure de début du cours
            $table->time('heure_fin')->nullable(); // Heure de fin du cours
            $table->enum('statut_presence', ['present', 'absent', 'retard', 'exclusion', 'dispense', 'autre'])->default('present');
            $table->enum('type_cours', ['cours_normal', 'examen', 'controle', 'activite', 'autre'])->default('cours_normal');
            
            // Détails du retard ou absence
            $table->integer('minutes_retard')->nullable(); // Minutes de retard
            $table->text('motif_absence')->nullable(); // Motif de l'absence
            $table->text('justification')->nullable(); // Justification fournie
            $table->boolean('justification_validee')->default(false); // Justification validée
            
            // Saisie et validation
            $table->foreignId('saisie_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_saisie')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_saisie')->nullable();
            $table->text('commentaires_validation')->nullable();
            
            // Notifications
            $table->boolean('notification_parent_envoyee')->default(false);
            $table->timestamp('date_notification_parent')->nullable();
            $table->text('commentaires_notification')->nullable();
            
            // Suivi
            $table->boolean('sanction_appliquee')->default(false);
            $table->text('details_sanction')->nullable();
            $table->date('date_sanction')->nullable();
            
            // Métadonnées
            $table->string('periode_scolaire')->nullable(); // Période scolaire (trimestre, semestre)
            $table->integer('semaine_scolaire')->nullable(); // Numéro de semaine scolaire
            $table->integer('jour_semaine')->nullable(); // Jour de la semaine (1-7)
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'date_presence']);
            $table->index(['classe_id', 'date_presence']);
            $table->index(['matiere_id', 'date_presence']);
            $table->index(['enseignant_id', 'date_presence']);
            $table->index(['annee_scolaire_id', 'date_presence']);
            $table->index('statut_presence');
            $table->index('type_cours');
            $table->index('date_presence');
            $table->index('justification_validee');
            $table->index('notification_parent_envoyee');
            $table->index('sanction_appliquee');
            $table->index('saisie_par_id');
            $table->index('valide_par_id');
            $table->index('periode_scolaire');
            $table->index('semaine_scolaire');
            
            // Contrainte unique : un élève ne peut avoir qu'une présence par matière/jour
            $table->unique(['eleve_id', 'matiere_id', 'date_presence'], 'uk_presence_eleve_matiere_jour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences_eleves');
    }
};
