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
        Schema::create('bulletins_eleves', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison principales
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('restrict');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Informations du bulletin
            $table->string('numero_bulletin')->unique(); // Numéro unique du bulletin
            $table->enum('type_bulletin', ['trimestre', 'semestre', 'annuel', 'intermediaire'])->default('trimestre');
            $table->enum('statut', ['en_preparation', 'valide', 'publie', 'archivé'])->default('en_preparation');
            
            // Moyennes générales
            $table->decimal('moyenne_generale_francais', 5, 2)->nullable(); // Moyenne générale programme français
            $table->decimal('moyenne_generale_arabe', 5, 2)->nullable(); // Moyenne générale programme arabe
            $table->decimal('moyenne_generale_totale', 5, 2)->nullable(); // Moyenne générale tous programmes
            $table->decimal('moyenne_generale_ponderee', 5, 2)->nullable(); // Moyenne pondérée par coefficients
            
            // Classements
            $table->integer('rang_classe_francais')->nullable(); // Rang dans la classe (programme français)
            $table->integer('rang_classe_arabe')->nullable(); // Rang dans la classe (programme arabe)
            $table->integer('rang_niveau_francais')->nullable(); // Rang dans le niveau (programme français)
            $table->integer('rang_niveau_arabe')->nullable(); // Rang dans le niveau (programme arabe)
            $table->integer('effectif_classe')->nullable(); // Effectif de la classe
            $table->integer('effectif_niveau_francais')->nullable(); // Effectif du niveau français
            $table->integer('effectif_niveau_arabe')->nullable(); // Effectif du niveau arabe
            
            // Statistiques de présence
            $table->integer('nombre_jours_presence')->default(0);
            $table->integer('nombre_jours_absence')->default(0);
            $table->integer('nombre_jours_retard')->default(0);
            $table->decimal('taux_presence', 5, 2)->nullable(); // Pourcentage de présence
            
            // Appréciations générales
            $table->text('appreciation_generale_francais')->nullable(); // Appréciation programme français
            $table->text('appreciation_generale_arabe')->nullable(); // Appréciation programme arabe
            $table->text('appreciation_generale_totale')->nullable(); // Appréciation générale
            $table->text('conseils_orientation')->nullable(); // Conseils d'orientation
            
            // Gestion administrative
            $table->foreignId('redige_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('publie_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_redaction')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_publication')->nullable();
            $table->timestamp('date_consultation_parents')->nullable();
            
            // Commentaires et notes
            $table->text('commentaires_redaction')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->text('commentaires_publication')->nullable();
            $table->text('notes_internes')->nullable(); // Notes pour l'administration
            
            // Métadonnées du document
            $table->string('fichier_pdf')->nullable(); // Chemin vers le PDF généré
            $table->string('hash_fichier')->nullable(); // Hash pour vérifier l'intégrité
            $table->integer('taille_fichier')->nullable(); // Taille en octets
            $table->json('metadonnees_generation')->nullable(); // Métadonnées de génération
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['eleve_id', 'periode_id']);
            $table->index(['classe_id', 'periode_id']);
            $table->index('type_bulletin');
            $table->index('statut');
            $table->index('date_redaction');
            $table->index('date_validation');
            $table->index('date_publication');
            $table->index('redige_par_id');
            $table->index('valide_par_id');
            $table->index('publie_par_id');
            
            // Contrainte unique : un seul bulletin par élève/période
            $table->unique(['eleve_id', 'periode_id'], 'uk_bulletin_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_eleves');
    }
};
