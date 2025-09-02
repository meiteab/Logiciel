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
        Schema::create('enseignants_matieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('cascade');
            $table->enum('niveau_competence', ['debutant', 'intermediaire', 'expert'])->default('intermediaire');
            $table->date('date_obtention_competence')->nullable();
            $table->text('commentaires_competence')->nullable();
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Un enseignant ne peut avoir qu'une seule entrée par matière
            $table->unique(['personnel_id', 'matiere_id'], 'uk_enseignant_matiere');
            
            $table->index('personnel_id');
            $table->index('matiere_id');
            $table->index('niveau_competence');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enseignants_matieres');
    }
};
