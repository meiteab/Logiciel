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
        Schema::create('documents_generes', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison principales
            $table->foreignId('type_document_id')->constrained('types_documents')->onDelete('restrict');
            $table->foreignId('template_document_id')->constrained('templates_documents')->onDelete('restrict');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');
            
            // Contexte de génération
            $table->foreignId('eleve_id')->nullable()->constrained('eleves')->onDelete('cascade'); // Pour les documents individuels
            $table->foreignId('classe_id')->nullable()->constrained('classes')->onDelete('cascade'); // Pour les documents de classe
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('cascade'); // Pour les documents de niveau
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->onDelete('cascade'); // Pour les documents de programme
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('cascade'); // Pour les documents périodiques
            
            // Informations du document
            $table->string('numero_document')->unique(); // Numéro unique du document
            $table->string('titre_document'); // Titre du document généré
            $table->text('description')->nullable(); // Description du document
            
            // Statut et cycle de vie
            $table->enum('statut', ['en_preparation', 'en_generation', 'genere', 'valide', 'publie', 'archive', 'erreur'])->default('en_preparation');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->boolean('est_urgent')->default(false); // Document urgent ou non
            
            // Fichiers générés
            $table->string('fichier_principal')->nullable(); // Chemin vers le fichier principal (PDF, DOCX, etc.)
            $table->string('fichier_annexe')->nullable(); // Fichier annexe si applicable
            $table->string('fichier_preview')->nullable(); // Image de prévisualisation
            $table->string('hash_fichier')->nullable(); // Hash pour vérifier l'intégrité
            $table->integer('taille_fichier')->nullable(); // Taille en octets
            
            // Métadonnées de génération
            $table->json('variables_utilisees')->nullable(); // Variables utilisées dans le template (JSON)
            $table->json('parametres_generation')->nullable(); // Paramètres de génération (JSON)
            $table->json('metadonnees_document')->nullable(); // Métadonnées du document (JSON)
            $table->text('log_generation')->nullable(); // Log de la génération
            
            // Gestion des erreurs
            $table->text('erreur_generation')->nullable(); // Erreur lors de la génération
            $table->integer('nombre_tentatives')->default(0); // Nombre de tentatives de génération
            $table->timestamp('derniere_tentative')->nullable(); // Date de la dernière tentative
            
            // Gestion des versions
            $table->integer('version_document')->default(1); // Version du document
            $table->boolean('est_derniere_version')->default(true); // Dernière version ou non
            $table->text('notes_version')->nullable(); // Notes sur cette version
            
            // Gestion des permissions
            $table->boolean('est_visible_enseignants')->default(true);
            $table->boolean('est_visible_administration')->default(true);
            $table->boolean('est_visible_parents')->default(false);
            $table->boolean('est_visible_eleves')->default(false);
            
            // Gestion administrative
            $table->foreignId('demande_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('genere_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('publie_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            
            // Dates importantes
            $table->timestamp('date_demande')->nullable();
            $table->timestamp('date_debut_generation')->nullable();
            $table->timestamp('date_fin_generation')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->timestamp('date_publication')->nullable();
            $table->timestamp('date_consultation')->nullable();
            
            // Commentaires et notes
            $table->text('commentaires_demande')->nullable();
            $table->text('commentaires_generation')->nullable();
            $table->text('commentaires_validation')->nullable();
            $table->text('commentaires_publication')->nullable();
            $table->text('notes_internes')->nullable();
            
            // Statistiques d'utilisation
            $table->integer('nombre_consultations')->default(0);
            $table->integer('nombre_telechargements')->default(0);
            $table->timestamp('derniere_consultation')->nullable();
            $table->timestamp('dernier_telechargement')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['type_document_id', 'statut']);
            $table->index(['eleve_id', 'type_document_id']);
            $table->index(['classe_id', 'type_document_id']);
            $table->index(['programme_id', 'annee_scolaire_id']);
            $table->index(['periode_id', 'statut']);
            $table->index('statut');
            $table->index('priorite');
            $table->index('est_urgent');
            $table->index('date_demande');
            $table->index('date_debut_generation');
            $table->index('date_validation');
            $table->index('date_publication');
            $table->index('demande_par_id');
            $table->index('genere_par_id');
            $table->index('valide_par_id');
            $table->index('publie_par_id');
            
            // Contrainte unique : un seul document par contexte et type
            $table->unique(['eleve_id', 'type_document_id', 'periode_id', 'version_document'], 'uk_doc_eleve_type_periode_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents_generes');
    }
};
