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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('code')->unique(); // Ex: CT1, EXAM1, DEV1
            $table->string('nom'); // Ex: "Contrôle 1", "Examen de fin de trimestre"
            $table->text('description')->nullable();
            
            // Type et catégorie
            $table->enum('type', ['controle', 'examen', 'devoir', 'interrogation', 'tp', 'autre'])->default('controle');
            $table->enum('categorie', ['ecrit', 'oral', 'pratique', 'mixte'])->default('ecrit');
            
            // Coefficient et pondération
            $table->decimal('coefficient', 3, 2)->default(1.00); // Coefficient de l'évaluation
            $table->decimal('note_maximale', 5, 2)->default(20.00); // Note maximale (20, 10, etc.)
            
            // Période et dates
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('restrict');
            $table->date('date_evaluation'); // Date de l'évaluation
            $table->date('date_limite_saisie')->nullable(); // Date limite pour saisir les notes
            
            // Matière et niveau
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('restrict');
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Statut et validation
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
            $table->boolean('est_obligatoire')->default(true);
            $table->boolean('est_visible_parents')->default(true);
            
            // Informations pédagogiques
            $table->text('objectifs_evaluation')->nullable();
            $table->text('consignes')->nullable();
            $table->text('criteres_evaluation')->nullable();
            
            // Gestion administrative
            $table->foreignId('enseignant_id')->constrained('personnels')->onDelete('restrict');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_validation')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['periode_id', 'matiere_id', 'niveau_id']);
            $table->index(['programme_id', 'annee_scolaire_id']);
            $table->index(['type', 'categorie']);
            $table->index('date_evaluation');
            $table->index('statut');
            $table->index('enseignant_id');
            $table->index('valide_par_id');
            $table->unique(['matiere_id', 'niveau_id', 'programme_id', 'periode_id', 'type'], 'uk_evaluation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
