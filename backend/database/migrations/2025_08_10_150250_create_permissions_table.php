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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: USER_CREATE, USER_READ, USER_UPDATE, USER_DELETE
            $table->string('nom'); // Ex: "Créer un utilisateur", "Lire un utilisateur"
            $table->text('description')->nullable(); // Description de la permission
            $table->enum('categorie', ['utilisateur', 'profil', 'role', 'permission', 'annee_scolaire', 'niveau', 'matiere', 'classe', 'emploi_temps', 'note', 'bulletin', 'frais', 'paiement', 'notification', 'parametre', 'autre'])->default('autre');
            $table->enum('niveau', ['systeme', 'application', 'module', 'fonction'])->default('fonction');
            $table->string('module')->nullable(); // Module concerné (ex: "Gestion des utilisateurs")
            $table->string('action')->nullable(); // Action concernée (ex: "create", "read", "update", "delete")
            $table->boolean('est_permission_systeme')->default(false); // Permission système ou personnalisée
            $table->boolean('peut_etre_modifiee')->default(true); // La permission peut-elle être modifiée
            $table->boolean('peut_etre_supprimee')->default(true); // La permission peut-elle être supprimée
            $table->text('conditions_application')->nullable(); // Conditions d'application en JSON
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Permission active ou non
            $table->timestamps();
            $table->index(['categorie', 'niveau']);
            $table->index(['module', 'action']);
            $table->index('est_permission_systeme');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
