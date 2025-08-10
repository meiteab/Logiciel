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
        Schema::create('types_frais', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: INSCRIPTION, SCOLARITE, CANTINE, UNIFORME
            $table->string('nom'); // Ex: "Frais d'inscription", "Frais de scolarité", "Cantine"
            $table->text('description')->nullable(); // Description détaillée
            $table->enum('categorie', ['inscription', 'scolarite', 'services', 'fournitures', 'activites', 'autre'])->default('scolarite');
            $table->enum('frequence', ['unique', 'annuelle', 'trimestrielle', 'mensuelle', 'hebdomadaire'])->default('annuelle');
            $table->boolean('est_obligatoire')->default(true); // Frais obligatoire ou optionnel
            $table->boolean('est_remboursable')->default(false); // Frais remboursable ou non
            $table->boolean('est_taxable')->default(false); // Frais avec TVA ou non
            $table->decimal('taux_tva', 5, 2)->default(0.00); // Taux de TVA en pourcentage
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Type de frais actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['categorie', 'frequence']);
            $table->index('est_obligatoire');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_frais');
    }
}; 