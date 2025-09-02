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
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ADMIN, ENSEIGNANT, ELEVE, PARENT, PERSONNEL
            $table->string('nom'); // Nom du profil (ex: "Administrateur", "Enseignant")
            $table->text('description')->nullable(); // Description du profil
            $table->enum('categorie', ['administratif', 'pedagogique', 'eleve', 'parent', 'autre'])->default('autre');
            $table->integer('niveau_acces')->default(1); // Niveau d'accès (1=bas, 10=élevé)
            $table->boolean('peut_gerer_utilisateurs')->default(false); // Peut gérer d'autres utilisateurs
            $table->boolean('peut_gerer_parametres')->default(false); // Peut modifier les paramètres système
            $table->boolean('peut_gerer_finances')->default(false); // Peut gérer les aspects financiers
            $table->boolean('peut_gerer_pedagogie')->default(false); // Peut gérer les aspects pédagogiques
            $table->json('permissions_specifiques')->nullable(); // Permissions spécifiques en JSON
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Profil actif ou non
            $table->timestamps();
            $table->softDeletes(); // Ajouter softDeletes
            $table->index(['categorie', 'niveau_acces']);
            $table->index('est_actif');
            $table->index('code'); // Ajouter index sur code
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
