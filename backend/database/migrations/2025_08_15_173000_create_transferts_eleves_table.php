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
        Schema::create('transferts_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_depart_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('classe_arrivee_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('restrict');
            
            // Date et motif du transfert
            $table->date('date_transfert');
            $table->enum('motif_transfert', ['redoublement', 'changement_programme', 'demande_parent', 'administratif']);
            $table->text('description')->nullable();
            
            // Validation du transfert
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->enum('statut', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('eleve_id');
            $table->index('classe_depart_id');
            $table->index('classe_arrivee_id');
            $table->index('date_transfert');
            $table->index('motif_transfert');
            $table->index('statut');
            $table->index('valide_par_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferts_eleves');
    }
}; 