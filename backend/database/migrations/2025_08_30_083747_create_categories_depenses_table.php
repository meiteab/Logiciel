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
        Schema::create('categories_depenses', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('code')->unique(); // Ex: MATERIEL, SALAIRES, MAINTENANCE
            $table->string('nom'); // Ex: "Matériel pédagogique", "Salaires", "Maintenance"
            $table->text('description')->nullable(); // Description détaillée
            $table->enum('type', ['fonctionnement', 'investissement', 'personnel', 'pedagogique', 'maintenance', 'autre'])->default('fonctionnement');
            
            // Gestion budgétaire
            $table->decimal('budget_annuel', 12, 2)->nullable(); // Budget annuel alloué
            $table->decimal('seuil_alerte', 12, 2)->nullable(); // Seuil d'alerte (80% du budget)
            $table->boolean('budget_renouvelable')->default(true); // Budget renouvelable ou non
            $table->boolean('budget_annuel_obligatoire')->default(false); // Budget annuel obligatoire
            
            // Contrôles
            $table->boolean('validation_obligatoire')->default(false); // Validation obligatoire avant dépense
            $table->foreignId('responsable_id')->nullable()->constrained('personnels')->onDelete('set null'); // Responsable de la catégorie
            $table->text('conditions_validation')->nullable(); // Conditions de validation
            
            // Informations administratives
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Catégorie active ou non
            $table->integer('ordre_affichage')->default(0); // Ordre d'affichage
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index('code');
            $table->index('type');
            $table->index('est_actif');
            $table->index('ordre_affichage');
            $table->index('responsable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories_depenses');
    }
};
