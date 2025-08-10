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
        Schema::create('jours_semaine', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: LUNDI, MARDI, MERCREDI, JEUDI, VENDREDI
            $table->string('nom'); // Ex: "Lundi", "Mardi", "Mercredi"
            $table->string('nom_court')->nullable(); // Ex: "Lun", "Mar", "Mer"
            $table->integer('numero_jour')->unique(); // 1=Lundi, 2=Mardi, 3=Mercredi...
            $table->boolean('est_jour_cours')->default(true); // Jour avec cours ou non
            $table->time('heure_debut_cours')->nullable(); // Heure de début des cours
            $table->time('heure_fin_cours')->nullable(); // Heure de fin des cours
            $table->time('heure_debut_pause')->nullable(); // Heure de début de la pause
            $table->time('heure_fin_pause')->nullable(); // Heure de fin de la pause
            $table->text('notes')->nullable(); // Notes spéciales pour ce jour
            $table->boolean('est_actif')->default(true); // Jour actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('numero_jour');
            $table->index('est_jour_cours');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jours_semaine');
    }
}; 