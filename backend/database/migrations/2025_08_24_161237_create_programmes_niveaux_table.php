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
        Schema::create('programmes_niveaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Structure du programme par niveau
            $table->integer('ordre_progression')->default(0); // Ordre de progression dans le programme
            $table->integer('duree_niveau')->default(1); // Durée en années (1 = 1 an, 2 = 2 ans)
            $table->integer('niveau_precedent_id')->nullable()->constrained('niveaux')->onDelete('set null'); // Niveau précédent requis
            $table->integer('niveau_suivant_id')->nullable()->constrained('niveaux')->onDelete('set null'); // Niveau suivant logique
            
            // Configuration pédagogique
            $table->integer('nombre_eleves_max')->nullable(); // Nombre maximum d'élèves par classe
            $table->integer('nombre_eleves_min')->nullable(); // Nombre minimum d'élèves par classe
            $table->integer('heures_total_semaine')->nullable(); // Total des heures par semaine
            $table->integer('heures_total_annee')->nullable(); // Total des heures par année
            
            // Conditions et prérequis
            $table->text('conditions_admission')->nullable(); // Conditions d'admission à ce niveau
            $table->text('prerequis_competences')->nullable(); // Compétences requises
            $table->text('objectifs_generaux')->nullable(); // Objectifs généraux du niveau
            
            // Validation et statut
            $table->boolean('est_niveau_obligatoire')->default(true); // Niveau obligatoire dans le programme
            $table->boolean('est_niveau_test')->default(false); // Niveau de test/évaluation
            $table->enum('statut', ['actif', 'inactif', 'en_preparation', 'archive'])->default('actif');
            
            // Notes et commentaires
            $table->text('notes_pedagogiques')->nullable(); // Notes spécifiques au niveau
            $table->text('commentaires_administration')->nullable(); // Commentaires administratifs
            
            $table->timestamps();
            $table->softDeletes();
            
            // Contrainte unique : un programme ne peut avoir qu'une entrée par niveau/année
            $table->unique(['programme_id', 'niveau_id', 'annee_scolaire_id'], 'uk_programme_niveau_annee');
            
            // Index pour améliorer les performances
            $table->index(['programme_id', 'annee_scolaire_id']);
            $table->index(['niveau_id', 'annee_scolaire_id']);
            $table->index('ordre_progression');
            $table->index('niveau_precedent_id');
            $table->index('niveau_suivant_id');
            $table->index('est_niveau_obligatoire');
            $table->index('est_niveau_test');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes_niveaux');
    }
};
