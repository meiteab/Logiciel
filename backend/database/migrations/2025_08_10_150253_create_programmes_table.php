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
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: FRANCAIS, ARABE, BILINGUE
            $table->string('nom'); // Ex: "Programme Français", "Programme Arabe"
            $table->text('description')->nullable(); // Description du programme
            $table->enum('categorie', ['francais', 'arabe', 'bilingue', 'international', 'autre'])->default('francais');
            $table->string('langue_principale')->default('fr'); // Langue principale d'enseignement
            $table->string('langue_secondaire')->nullable(); // Langue secondaire d'enseignement
            $table->boolean('est_programme_par_defaut')->default(false); // Programme par défaut
            $table->boolean('est_programme_bilingue')->default(false); // Programme bilingue
            $table->integer('nombre_heures_semaine')->nullable(); // Nombre d'heures par semaine
            $table->text('matieres_obligatoires')->nullable(); // Matières obligatoires en JSON
            $table->text('matieres_optionnelles')->nullable(); // Matières optionnelles en JSON
            $table->text('conditions_admission')->nullable(); // Conditions d'admission
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Programme actif ou non
            $table->timestamps();
            $table->index(['categorie', 'langue_principale']);
            $table->index('est_programme_par_defaut');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
