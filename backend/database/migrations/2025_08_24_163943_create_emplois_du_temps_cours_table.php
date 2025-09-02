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
        Schema::create('emplois_du_temps_cours', function (Blueprint $table) {
            $table->id();
            // Clés de liaison principales
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('jour_semaine_id')->constrained('jours_semaine')->onDelete('restrict');
            $table->foreignId('plage_horaire_id')->constrained('plages_horaires')->onDelete('restrict');
            $table->foreignId('matiere_id')->constrained('matieres')->onDelete('restrict');
            $table->foreignId('enseignant_id')->constrained('personnels')->onDelete('restrict');
            $table->foreignId('salle_id')->constrained('salles')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations sur le cours
            $table->enum('type_cours', ['cours', 'td', 'tp', 'evaluation', 'revision', 'autre'])->default('cours');
            $table->enum('statut', ['planifie', 'confirme', 'annule', 'termine'])->default('planifie');
            $table->text('commentaires')->nullable(); // Commentaires sur le cours
            
            // Gestion des exceptions
            $table->boolean('est_exception')->default(false); // Si c'est une modification temporaire
            $table->date('date_exception')->nullable(); // Date spécifique si exception
            $table->text('motif_exception')->nullable(); // Motif de l'exception
            
            // Validation et suivi
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('notes_validation')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Contraintes uniques pour éviter les conflits
            // 1. Une classe ne peut avoir qu'un cours par jour/plage/année
            $table->unique(['classe_id', 'jour_semaine_id', 'plage_horaire_id', 'annee_scolaire_id'], 'uk_classe_jour_plage_annee');
            
            // 2. Un enseignant ne peut avoir qu'un cours par jour/plage/année
            $table->unique(['enseignant_id', 'jour_semaine_id', 'plage_horaire_id', 'annee_scolaire_id'], 'uk_enseignant_jour_plage_annee');
            
            // 3. Une salle ne peut avoir qu'un cours par jour/plage/année
            $table->unique(['salle_id', 'jour_semaine_id', 'plage_horaire_id', 'annee_scolaire_id'], 'uk_salle_jour_plage_annee');
            
            // Index pour améliorer les performances
            $table->index('classe_id');
            $table->index('jour_semaine_id');
            $table->index('plage_horaire_id');
            $table->index('matiere_id');
            $table->index('enseignant_id');
            $table->index('salle_id');
            $table->index('annee_scolaire_id');
            $table->index('type_cours');
            $table->index('statut');
            $table->index('est_exception');
            $table->index('date_exception');
            $table->index('valide_par_id');
            
            // Index composites pour les requêtes fréquentes
            $table->index(['classe_id', 'annee_scolaire_id']);
            $table->index(['enseignant_id', 'annee_scolaire_id']);
            $table->index(['jour_semaine_id', 'plage_horaire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emplois_du_temps_cours');
    }
};
