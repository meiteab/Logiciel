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
        Schema::create('enseignants_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            $table->enum('role', ['titulaire', 'suppleant', 'remplacant'])->default('titulaire');
            $table->integer('heures_semaine')->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->text('notes_pedagogiques')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Un enseignant ne peut enseigner qu'une matière dans une classe donnée pour une année donnée
            $table->unique(['personnel_id', 'classe_id', 'matiere_id', 'annee_scolaire_id'], 'uk_enseignant_classe_matiere_annee');
            
            $table->index('personnel_id');
            $table->index('classe_id');
            $table->index('matiere_id');
            $table->index('annee_scolaire_id');
            $table->index('role');
            $table->index('statut');
            $table->index('date_debut');
            $table->index('date_fin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants_classes');
    }
};
