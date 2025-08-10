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
        Schema::create('grilles_tarifaires', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: GRILLE_2024_2025, GRILLE_CP, GRILLE_CE1
            $table->string('nom'); // Ex: "Grille tarifaire 2024-2025", "Grille CP"
            $table->text('description')->nullable(); // Description de la grille
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('set null');
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->onDelete('set null');
            $table->date('date_debut_validite'); // Date de début de validité
            $table->date('date_fin_validite')->nullable(); // Date de fin de validité
            $table->boolean('est_grille_par_defaut')->default(false); // Grille par défaut ou spéciale
            $table->text('conditions_particulieres')->nullable(); // Conditions spéciales
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Grille active ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['annee_scolaire_id', 'niveau_id']);
            $table->index(['date_debut_validite', 'date_fin_validite']);
            $table->index('est_grille_par_defaut');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grilles_tarifaires');
    }
}; 