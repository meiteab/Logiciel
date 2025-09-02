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
        Schema::create('remplacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absence_enseignant_id')->constrained('absences_enseignants')->onDelete('cascade');
            $table->foreignId('enseignant_remplacant_id')->constrained('personnels')->onDelete('restrict');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->date('date_remplacement');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->enum('statut', ['planifie', 'confirme', 'annule', 'termine'])->default('planifie');
            $table->text('notes_remplacement')->nullable();
            $table->text('contenu_cours')->nullable();
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('absence_enseignant_id');
            $table->index('enseignant_remplacant_id');
            $table->index('classe_id');
            $table->index('matiere_id');
            $table->index('date_remplacement');
            $table->index('statut');
            $table->index('valide_par_id');
            $table->index(['date_remplacement', 'heure_debut', 'heure_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remplacements');
    }
};
