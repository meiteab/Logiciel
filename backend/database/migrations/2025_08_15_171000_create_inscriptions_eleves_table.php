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
        Schema::create('inscriptions_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            
            // DEUX colonnes pour les classes (française ET arabe)
            $table->foreignId('classe_francaise_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('classe_arabe_id')->constrained('classes')->onDelete('restrict');
            
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('restrict');
            
            // Dates d'inscription et de sortie
            $table->date('date_inscription');
            $table->date('date_sortie')->nullable();
            
            // Type d'inscription (nouvelle, réinscription, redoublement, transfert)
            $table->enum('type_inscription', ['nouvelle', 'reinscription','transfert'])->default('nouvelle');
            
            // Statut de l'inscription
            $table->enum('statut', ['inscrit', 'redoublement', 'transfert', 'sortie', 'suspendu'])->default('inscrit');
            
            // Informations supplémentaires
            $table->text('motif_sortie')->nullable();
            $table->text('notes_administratives')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Contrainte unique : un élève ne peut être inscrit qu'une fois par année
            $table->unique(['eleve_id', 'annee_scolaire_id'], 'uk_eleve_annee');
            
            // Index pour améliorer les performances
            $table->index('eleve_id');
            $table->index('classe_francaise_id');
            $table->index('classe_arabe_id');
            $table->index('annee_scolaire_id');
            $table->index('type_inscription');
            $table->index('statut');
            $table->index('date_inscription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscriptions_eleves');
    }
}; 