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
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: T1, T2, T3, S1, S2
            $table->string('nom'); // Ex: "1er Trimestre", "2ème Trimestre"
            $table->text('description')->nullable(); // Description de la période
            $table->enum('type', ['trimestre', 'semestre', 'mois', 'autre'])->default('trimestre');
            $table->integer('ordre')->default(0); // Ordre dans l'année (1, 2, 3...)
            $table->date('date_debut'); // Date de début de la période
            $table->date('date_fin'); // Date de fin de la période
            $table->boolean('est_periode_notes')->default(true); // Période pour saisir les notes
            $table->boolean('est_periode_bulletins')->default(false); // Période pour les bulletins
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->boolean('est_actif')->default(true); // Période active ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['annee_scolaire_id', 'ordre']);
            $table->index(['date_debut', 'date_fin']);
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodes');
    }
}; 