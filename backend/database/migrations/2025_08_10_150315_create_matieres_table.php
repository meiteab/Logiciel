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
        Schema::create('matieres', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: MATHS, FRANCAIS, ARABE, CORAN
            $table->string('nom'); // Ex: "Mathématiques", "Français", "Arabe", "Coran"
            $table->text('description')->nullable(); // Description de la matière
            $table->enum('couleur', ['rouge', 'bleu', 'vert', 'jaune', 'orange', 'violet', 'rose', 'gris'])->default('bleu');
            $table->string('icone')->nullable(); // Icône pour l'interface (ex: "math", "book", "star")
            $table->integer('coefficient')->default(1); // Coefficient pour le calcul des moyennes
            $table->integer('ordre')->default(0); // Ordre d'affichage
            $table->boolean('est_matiere_principale')->default(true); // Matière principale ou optionnelle
            $table->boolean('est_matiere_notes')->default(true); // Matière avec notes ou sans
            $table->foreignId('programme_id')->constrained('programmes')->onDelete('cascade'); // FR ou AR
            $table->boolean('est_actif')->default(true); // Matière active ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['programme_id', 'ordre']);
            $table->index('est_actif');
            $table->index('est_matiere_principale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matieres');
    }
}; 