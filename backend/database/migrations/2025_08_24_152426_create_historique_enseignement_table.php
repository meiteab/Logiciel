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
        Schema::create('historique_enseignement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('restrict');
            $table->enum('role', ['titulaire', 'suppleant', 'remplacant'])->default('titulaire');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->integer('heures_total')->default(0);
            $table->integer('nombre_eleves')->default(0);
            $table->enum('statut_fin', ['normal', 'transfert', 'demission', 'retraite', 'autre'])->default('normal');
            $table->text('evaluation_performance')->nullable();
            $table->text('commentaires_fin')->nullable();
            $table->foreignId('evalue_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_evaluation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('personnel_id');
            $table->index('classe_id');
            $table->index('matiere_id');
            $table->index('annee_scolaire_id');
            $table->index('role');
            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('statut_fin');
            $table->index('evalue_par_id');
            $table->index(['personnel_id', 'annee_scolaire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historique_enseignement');
    }
};
