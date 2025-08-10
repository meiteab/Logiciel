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
        Schema::create('plages_horaires', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: P1, P2, P3, P4, RECRE, DEJEUNER
            $table->string('nom'); // Ex: "1ère Période", "2ème Période", "Récréation"
            $table->text('description')->nullable(); // Description de la plage
            $table->time('heure_debut'); // Heure de début (ex: 08:00:00)
            $table->time('heure_fin'); // Heure de fin (ex: 09:00:00)
            $table->integer('duree_minutes')->nullable(); // Durée en minutes (calculée)
            $table->enum('type', ['cours', 'recreation', 'pause_dejeuner', 'autre'])->default('cours');
            $table->integer('ordre')->default(0); // Ordre dans la journée
            $table->boolean('est_obligatoire')->default(true); // Plage obligatoire ou flexible
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Plage active ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['heure_debut', 'heure_fin']);
            $table->index('type');
            $table->index('ordre');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plages_horaires');
    }
}; 