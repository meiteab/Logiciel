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
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Lien optionnel vers user
            $table->string('matricule')->unique(); // Matricule unique
            $table->string('prenom'); // Prénom obligatoire
            $table->string('nom_famille'); // Nom de famille obligatoire
            $table->enum('civilite', ['M', 'Mme', 'Mlle'])->nullable(); // Civilité
            $table->date('date_naissance')->nullable(); // Date de naissance
            $table->enum('sexe', ['M', 'F'])->nullable(); // Genre
            $table->string('lieu_naissance')->nullable(); // Lieu de naissance

            // Informations santé basiques
            $table->text('infos_sante')->nullable(); // Informations de santé
            $table->string('photo')->nullable(); // Photo de l'élève
            $table->json('documents_obligatoires')->nullable(); // Documents personnels obligatoires
            $table->text('observations_pedagogiques')->nullable(); // Observations pédagogiques personnelles
            
            $table->timestamps();
            $table->softDeletes(); // Suppression logique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};
