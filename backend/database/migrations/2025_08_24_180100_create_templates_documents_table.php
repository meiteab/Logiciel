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
        Schema::create('templates_documents', function (Blueprint $table) {
            $table->id();
            
            // Clés de liaison
            $table->foreignId('type_document_id')->constrained('types_documents')->onDelete('cascade');
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->onDelete('set null'); // FR, AR, ou null pour tous
            
            // Informations du template
            $table->string('code')->unique(); // Ex: TEMPL_BULLETIN_FR_CP, TEMPL_CERTIFICAT_AR
            $table->string('nom'); // Ex: "Template Bulletin CP Français", "Template Certificat Arabe"
            $table->text('description')->nullable(); // Description du template
            
            // Version et statut
            $table->string('version')->default('1.0'); // Version du template
            $table->enum('statut', ['brouillon', 'en_test', 'actif', 'deprecie', 'archive'])->default('brouillon');
            $table->boolean('est_template_par_defaut')->default(false); // Template par défaut pour ce type
            
            // Fichiers du template
            $table->string('fichier_template')->nullable(); // Chemin vers le fichier template (HTML, DOCX, etc.)
            $table->string('fichier_css')->nullable(); // Fichier CSS pour le style
            $table->string('fichier_js')->nullable(); // Fichier JavaScript pour l'interactivité
            $table->string('fichier_preview')->nullable(); // Image de prévisualisation
            
            // Configuration du template
            $table->json('variables_disponibles')->nullable(); // Variables disponibles (JSON)
            $table->json('sections_template')->nullable(); // Sections du template (JSON)
            $table->json('styles_disponibles')->nullable(); // Styles disponibles (JSON)
            $table->json('parametres_generation')->nullable(); // Paramètres de génération (JSON)
            
            // Gestion des langues
            $table->string('langue_principale')->default('fr'); // Langue principale du template
            $table->json('langues_supportees')->nullable(); // Langues supportées (JSON)
            $table->boolean('est_multilingue')->default(false); // Support multilingue
            
            // Gestion des permissions
            $table->boolean('est_visible_enseignants')->default(true);
            $table->boolean('est_visible_administration')->default(true);
            $table->boolean('est_visible_parents')->default(false);
            $table->boolean('est_visible_eleves')->default(false);
            
            // Informations techniques
            $table->text('instructions_utilisation')->nullable(); // Instructions pour utiliser le template
            $table->text('notes_techniques')->nullable(); // Notes techniques
            $table->json('dependances')->nullable(); // Dépendances techniques (JSON)
            
            // Gestion administrative
            $table->foreignId('cree_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->timestamp('date_validation')->nullable();
            $table->text('commentaires_validation')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['type_document_id', 'programme_id']);
            $table->index(['statut', 'est_template_par_defaut']);
            $table->index('version');
            $table->index('langue_principale');
            $table->index('cree_par_id');
            $table->index('valide_par_id');
            $table->index('date_validation');
            
            // Contrainte unique : un seul template par défaut par type et programme
            $table->unique(['type_document_id', 'programme_id', 'est_template_par_defaut'], 'uk_template_defaut_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates_documents');
    }
};
