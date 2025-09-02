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
        Schema::create('types_documents', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('code')->unique(); // Ex: BULLETIN, CERTIFICAT_SCOLARITE, CERTIFICAT_TRANSFERT
            $table->string('nom'); // Ex: "Bulletin de notes", "Certificat de scolarité"
            $table->text('description')->nullable(); // Description détaillée du type de document
            
            // Catégorie et contexte
            $table->enum('categorie', ['pedagogique', 'administratif', 'financier', 'statistique', 'autre'])->default('pedagogique');
            $table->enum('contexte', ['eleve', 'classe', 'niveau', 'programme', 'annee', 'global'])->default('eleve');
            $table->boolean('est_obligatoire')->default(false); // Document obligatoire ou optionnel
            $table->boolean('est_visible_parents')->default(true); // Visible par les parents
            
            // Gestion des périodes
            $table->boolean('est_periodique')->default(false); // Généré périodiquement
            $table->enum('frequence_generation', ['trimestre', 'semestre', 'annuel', 'ponctuel', 'sur_demande'])->default('ponctuel');
            $table->json('periodes_autorisees')->nullable(); // Périodes autorisées (JSON)
            
            // Configuration de génération
            $table->string('format_sortie')->default('pdf'); // pdf, docx, html, etc.
            $table->boolean('est_editable')->default(false); // Peut être modifié après génération
            $table->boolean('est_archivable')->default(true); // Doit être archivé
            $table->integer('duree_conservation_mois')->default(60); // Durée de conservation en mois
            
            // Gestion des versions
            $table->string('version_template')->default('1.0'); // Version du template
            $table->boolean('est_version_active')->default(true); // Version active ou non
            $table->text('notes_version')->nullable(); // Notes sur cette version
            
            // Métadonnées
            $table->json('champs_obligatoires')->nullable(); // Champs requis (JSON)
            $table->json('champs_optionnels')->nullable(); // Champs optionnels (JSON)
            $table->text('conditions_generation')->nullable(); // Conditions pour générer le document
            $table->text('notes_techniques')->nullable(); // Notes techniques pour les développeurs
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['categorie', 'contexte']);
            $table->index('est_periodique');
            $table->index('frequence_generation');
            $table->index('est_version_active');
            $table->index('est_obligatoire');
            $table->index('est_visible_parents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_documents');
    }
};
