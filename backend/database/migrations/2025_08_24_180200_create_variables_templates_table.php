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
        Schema::create('variables_templates', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('code')->unique(); // Ex: ELEVE_NOM, CLASSE_NOM, MOYENNE_GENERALE
            $table->string('nom'); // Ex: "Nom de l'élève", "Nom de la classe"
            $table->text('description')->nullable(); // Description de la variable
            
            // Type et catégorie
            $table->enum('type', ['texte', 'nombre', 'date', 'boolean', 'json', 'html'])->default('texte');
            $table->enum('categorie', ['eleve', 'classe', 'niveau', 'programme', 'annee', 'periode', 'matiere', 'note', 'statistique', 'systeme'])->default('eleve');
            
            // Contexte d'utilisation
            $table->json('types_documents_compatibles')->nullable(); // Types de documents qui peuvent utiliser cette variable
            $table->json('contextes_utilisation')->nullable(); // Contextes d'utilisation (JSON)
            $table->boolean('est_obligatoire')->default(false); // Variable obligatoire ou optionnelle
            
            // Valeur et format
            $table->string('valeur_defaut')->nullable(); // Valeur par défaut
            $table->string('format_affichage')->nullable(); // Format d'affichage (ex: "dd/mm/yyyy" pour les dates)
            $table->string('unite')->nullable(); // Unité de mesure (ex: "points", "euros", "%")
            $table->json('valeurs_possibles')->nullable(); // Valeurs possibles pour les ENUM (JSON)
            
            // Validation et contraintes
            $table->string('regex_validation')->nullable(); // Regex de validation
            $table->integer('longueur_min')->nullable(); // Longueur minimale
            $table->integer('longueur_max')->nullable(); // Longueur maximale
            $table->decimal('valeur_min', 10, 2)->nullable(); // Valeur minimale
            $table->decimal('valeur_max', 10, 2)->nullable(); // Valeur maximale
            
            // Gestion des erreurs
            $table->string('message_erreur')->nullable(); // Message d'erreur personnalisé
            $table->string('valeur_fallback')->nullable(); // Valeur de secours en cas d'erreur
            $table->boolean('est_critique')->default(false); // Erreur critique ou non
            
            // Informations techniques
            $table->text('exemple_utilisation')->nullable(); // Exemple d'utilisation dans le template
            $table->text('notes_techniques')->nullable(); // Notes techniques
            $table->json('dependances')->nullable(); // Variables dont celle-ci dépend (JSON)
            
            // Gestion des langues
            $table->boolean('est_multilingue')->default(false); // Support multilingue
            $table->json('traductions')->nullable(); // Traductions disponibles (JSON)
            
            // Métadonnées
            $table->string('source_donnees')->nullable(); // Table/relation source des données
            $table->string('champ_source')->nullable(); // Champ source dans la table
            $table->text('logique_calcul')->nullable(); // Logique de calcul si applicable
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['categorie', 'type']);
            $table->index('est_obligatoire');
            $table->index('est_critique');
            $table->index('est_multilingue');
            $table->index('source_donnees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables_templates');
    }
};
