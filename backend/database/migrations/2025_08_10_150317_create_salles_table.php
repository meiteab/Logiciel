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
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: A1, A2, LABO, SPORT, ADMIN
            $table->string('nom'); // Ex: "Salle A1", "Laboratoire Sciences", "Gymnase"
            $table->text('description')->nullable(); // Description de la salle
            $table->enum('type', ['classe', 'laboratoire', 'gymnase', 'bureau', 'salle_reunion', 'autre'])->default('classe');
            $table->integer('capacite_max')->nullable(); // Nombre max d'élèves
            $table->integer('etage')->nullable(); // Numéro d'étage
            $table->string('batiment')->nullable(); // Nom du bâtiment
            $table->string('zone')->nullable(); // Zone de l'école (ex: "Bâtiment A", "Bâtiment B")
            $table->boolean('est_climatisee')->default(false); // Salle climatisée ou non
            $table->boolean('est_equipee_informatique')->default(false); // Salle avec ordinateurs
            $table->text('equipements')->nullable(); // Liste des équipements
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Salle active ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['type', 'capacite_max']);
            $table->index(['batiment', 'etage']);
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salles');
    }
}; 