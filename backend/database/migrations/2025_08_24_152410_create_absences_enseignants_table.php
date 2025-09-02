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
        Schema::create('absences_enseignants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained('personnels')->onDelete('cascade');
            $table->date('date_absence');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->enum('type_absence', ['maladie', 'conges', 'formation', 'personnel', 'autre'])->default('maladie');
            $table->enum('statut', ['en_attente', 'validee', 'refusee'])->default('en_attente');
            $table->text('motif')->nullable();
            $table->text('justificatif')->nullable();
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('personnel_id');
            $table->index('date_absence');
            $table->index('type_absence');
            $table->index('statut');
            $table->index('valide_par_id');
            $table->index(['date_absence', 'heure_debut', 'heure_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences_enseignants');
    }
};
