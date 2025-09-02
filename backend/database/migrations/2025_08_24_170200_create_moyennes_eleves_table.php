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
        Schema::create('moyennes_eleves', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison principales
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('restrict');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Moyennes calculées
            $table->decimal('moyenne_notes', 5, 2)->nullable(); // Moyenne simple des notes
            $table->decimal('moyenne_ponderee', 5, 2)->nullable(); // Moyenne pondérée par coefficient
            $table->decimal('moyenne_coefficient', 5, 2)->nullable(); // Moyenne × coefficient matière
            
            // Statistiques détaillées
            $table->integer('nombre_evaluations')->default(0); // Nombre d'évaluations
            $table->integer('nombre_notes_validees')->default(0); // Nombre de notes validées
            $table->integer('nombre_absences')->default(0); // Nombre d'absences
            $table->integer('nombre_retards')->default(0); // Nombre de retards
            
            // Notes extrêmes
            $table->decimal('note_minimale', 5, 2)->nullable(); // Note la plus basse
            $table->decimal('note_maximale', 5, 2)->nullable(); // Note la plus haute
            
            // Classement et position
            $table->integer('rang_classe')->nullable(); // Rang dans la classe pour cette matière
            $table->integer('rang_niveau')->nullable(); // Rang dans le niveau pour cette matière
            $table->integer('effectif_classe')->nullable(); // Effectif de la classe
            $table->integer('effectif_niveau')->nullable(); // Effectif du niveau
            
            // Calculs et validations
            $table->boolean('est_calculee')->default(false); // Moyenne calculée ou non
            $table->boolean('est_validee')->default(false); // Moyenne validée par l'admin
            $table->timestamp('date_calcul')->nullable(); // Date du calcul
            $table->timestamp('date_validation')->nullable(); // Date de validation
            
            // Gestion administrative
            $table->foreignId('calculee_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('validee_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->text('commentaires_calcul')->nullable(); // Commentaires sur le calcul
            $table->text('commentaires_validation')->nullable(); // Commentaires de validation
            
            // Informations pédagogiques
            $table->text('appreciation_generale')->nullable(); // Appréciation générale
            $table->text('points_forts')->nullable(); // Points forts identifiés
            $table->text('points_amelioration')->nullable(); // Points à améliorer
            $table->text('conseils_pedagogiques')->nullable(); // Conseils pédagogiques
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'periode_id', 'matiere_id']);
            $table->index(['classe_id', 'programme_id', 'periode_id']);
            $table->index(['programme_id', 'annee_scolaire_id']);
            $table->index('est_calculee');
            $table->index('est_validee');
            $table->index('date_calcul');
            $table->index('date_validation');
            $table->index('calculee_par_id');
            $table->index('validee_par_id');
            
            // Contrainte unique : une seule moyenne par élève/matière/période/programme
            $table->unique(['eleve_id', 'matiere_id', 'periode_id', 'programme_id'], 'uk_moyenne_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moyennes_eleves');
    }
};
