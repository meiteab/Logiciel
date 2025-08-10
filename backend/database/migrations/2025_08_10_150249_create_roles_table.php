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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: ADMIN, ENSEIGNANT, ELEVE, PARENT, PERSONNEL
            $table->string('nom'); // Ex: "Administrateur", "Enseignant", "Élève"
            $table->text('description')->nullable(); // Description du rôle
            $table->enum('categorie', ['administratif', 'pedagogique', 'pedagogique', 'eleve', 'parent', 'autre'])->default('autre');
            $table->integer('niveau_priorite')->default(1); // Niveau de priorité (1=bas, 10=élevé)
            $table->boolean('est_role_systeme')->default(false); // Rôle système ou personnalisé
            $table->boolean('peut_etre_modifie')->default(true); // Le rôle peut-il être modifié
            $table->boolean('peut_etre_supprime')->default(true); // Le rôle peut-il être supprimé
            $table->text('permissions_par_defaut')->nullable(); // Permissions par défaut en JSON
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Rôle actif ou non
            $table->timestamps();
            $table->index(['categorie', 'niveau_priorite']);
            $table->index('est_role_systeme');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
