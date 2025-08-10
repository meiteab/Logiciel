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
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grille_tarifaire_id')->constrained('grilles_tarifaires')->onDelete('cascade');
            $table->foreignId('type_frais_id')->constrained('types_frais')->onDelete('cascade');
            $table->decimal('montant_ht', 10, 2); // Montant hors taxes
            $table->decimal('montant_ttc', 10, 2); // Montant toutes taxes comprises
            $table->decimal('taux_tva', 5, 2)->default(0.00); // Taux de TVA en pourcentage
            $table->string('devise')->default('EUR'); // Devise du tarif
            $table->enum('type_calcul', ['fixe', 'pourcentage', 'par_eleve', 'par_classe'])->default('fixe');
            $table->decimal('valeur_calcul', 10, 2)->nullable(); // Valeur pour le calcul (pourcentage, montant par élève, etc.)
            $table->text('conditions_particulieres')->nullable(); // Conditions spéciales
            $table->date('date_debut_validite'); // Date de début de validité
            $table->date('date_fin_validite')->nullable(); // Date de fin de validité
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Tarif actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['grille_tarifaire_id', 'type_frais_id']);
            $table->index(['date_debut_validite', 'date_fin_validite']);
            $table->index('type_calcul');
            $table->index('est_actif');
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['grille_tarifaire_id', 'type_frais_id', 'date_debut_validite'], 'unique_tarif_grille_type_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
}; 