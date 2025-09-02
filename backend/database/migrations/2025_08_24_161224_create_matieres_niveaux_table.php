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
        Schema::create('matieres_niveaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Données pédagogiques
            $table->integer('coefficient_niveau')->default(1); // Coefficient spécifique au niveau
            $table->integer('heures_semaine')->default(0); // Heures par semaine
            $table->integer('heures_annee')->default(0); // Heures totales par année
            $table->integer('ordre_matiere')->default(0); // Ordre d'affichage de la matière dans le niveau
            
            // Statut et validation
            $table->boolean('est_obligatoire')->default(true); // Matière obligatoire ou optionnelle
            $table->boolean('est_evaluee')->default(true); // Matière avec évaluation ou non
            $table->enum('statut', ['active', 'inactive', 'archivee'])->default('active');
            
            // Notes et commentaires
            $table->text('objectifs_pedagogiques')->nullable(); // Objectifs spécifiques au niveau
            $table->text('prerequis')->nullable(); // Prérequis pour ce niveau
            $table->text('commentaires')->nullable(); // Commentaires pédagogiques
            
            $table->timestamps();
            $table->softDeletes();
            
            // Contrainte unique : une matière ne peut avoir qu'une entrée par niveau/programme/année
            $table->unique(['matiere_id', 'niveau_id', 'programme_id', 'annee_scolaire_id'], 'uk_matiere_niveau_programme_annee');
            
            // Index pour améliorer les performances
            $table->index(['niveau_id', 'programme_id']);
            $table->index(['matiere_id', 'programme_id']);
            $table->index('annee_scolaire_id');
            $table->index('coefficient_niveau');
            $table->index('heures_semaine');
            $table->index('ordre_matiere');
            $table->index('est_obligatoire');
            $table->index('est_evaluee');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres_niveaux');
    }
};
