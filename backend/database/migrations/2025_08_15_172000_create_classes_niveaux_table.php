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
        Schema::create('classes_niveaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('restrict');
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('restrict');
            
            // Enseignant titulaire et salle
            $table->foreignId('enseignant_titulaire_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('salle_id')->nullable()->constrained('salles')->onDelete('set null');
            
            // Ordre d'affichage
            $table->integer('ordre_affichage')->default(0);
            
            $table->timestamps();
            
            // Contrainte unique : une classe ne peut avoir qu'un niveau/programme par année
            $table->unique(['classe_id', 'niveau_id', 'programme_id', 'annee_scolaire_id'], 'uk_classe_niveau_programme_annee');
            
            // Index pour améliorer les performances
            $table->index(['niveau_id', 'programme_id']);
            $table->index('enseignant_titulaire_id');
            $table->index('salle_id');
            $table->index('ordre_affichage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes_niveaux');
    }
}; 