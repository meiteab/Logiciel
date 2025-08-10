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
        Schema::create('annees_scolaires', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: 2024-2025, 2025-2026
            $table->string('nom'); // Ex: "Année scolaire 2024-2025"
            $table->text('description')->nullable(); // Description de l'année scolaire
            $table->date('date_debut'); // Date de début de l'année scolaire
            $table->date('date_fin'); // Date de fin de l'année scolaire
            $table->enum('statut', ['planifiee', 'en_cours', 'terminee', 'archivee'])->default('planifiee');
            $table->boolean('est_annee_courante')->default(false); // Année scolaire actuelle
            $table->boolean('est_annee_par_defaut')->default(false); // Année par défaut pour les nouvelles inscriptions
            $table->integer('nombre_trimestres')->default(3); // Nombre de trimestres
            $table->integer('nombre_semestres')->default(2); // Nombre de semestres
            $table->date('date_inscription_debut')->nullable(); // Date de début des inscriptions
            $table->date('date_inscription_fin')->nullable(); // Date de fin des inscriptions
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Année scolaire active ou non
            $table->timestamps();
            $table->index(['date_debut', 'date_fin']);
            $table->index('statut');
            $table->index('est_annee_courante');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annees_scolaires');
    }
};
