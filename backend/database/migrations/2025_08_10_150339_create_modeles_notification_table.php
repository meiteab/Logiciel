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
        Schema::create('modeles_notification', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: ABSENCE_ELEVE, RETARD_ELEVE, NOTE_NOUVELLE, PAIEMENT_RECU
            $table->string('nom'); // Ex: "Absence d'élève", "Nouvelle note", "Paiement reçu"
            $table->text('description')->nullable(); // Description du modèle
            $table->enum('categorie', ['pedagogique', 'administrative', 'financiere', 'securite', 'autre'])->default('pedagogique');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->string('sujet_email')->nullable(); // Sujet pour les emails
            $table->text('contenu_email')->nullable(); // Contenu du template email
            $table->text('contenu_sms')->nullable(); // Contenu du template SMS
            $table->text('contenu_push')->nullable(); // Contenu du template notification push
            $table->json('variables_template')->nullable(); // Variables disponibles dans le template
            $table->text('exemple_utilisation')->nullable(); // Exemple d'utilisation
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Modèle actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['categorie', 'priorite']);
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modeles_notification');
    }
}; 