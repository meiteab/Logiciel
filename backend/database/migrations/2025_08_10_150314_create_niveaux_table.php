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
        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: CP, CE1, CE2, CM1, CM2
            $table->string('nom'); // Ex: "Cours Préparatoire", "Cours Élémentaire 1"
            $table->text('description')->nullable(); // Description du niveau
            $table->integer('ordre')->default(0); // Ordre d'affichage (1, 2, 3...)
            $table->integer('age_min')->nullable(); // Âge minimum (ex: 6 ans)
            $table->integer('age_max')->nullable(); // Âge maximum (ex: 7 ans)
            $table->integer('capacite_max')->nullable(); // Nombre max d'élèves par classe
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('cascade'); // FR ou AR
            $table->boolean('est_actif')->default(true); // Niveau actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['programme_id', 'ordre']);
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveaux');
    }
}; 