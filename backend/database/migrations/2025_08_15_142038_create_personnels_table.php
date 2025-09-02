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
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            
            // Liaison avec table users (One-to-One)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
    
            // Informations personnelles
            $table->string('prenom');
            $table->string('nom_famille');
            $table->enum('civilite', ['M', 'Mme', 'Mlle'])->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->enum('sexe', ['M', 'F'])->nullable();
    
            // Informations professionnelles
            $table->string('matricule')->unique();
            $table->string('poste')->nullable();
            $table->date('date_embauche')->nullable();
            $table->date('date_depart')->nullable();
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'retraite'])->default('actif');
    
            // Contact
            $table->string('telephone')->nullable();
            $table->string('telephone_urgence')->nullable();
            $table->string('email_professionnel')->nullable();
            $table->string('adresse')->nullable();
            $table->string('ville')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('pays')->nullable();
    
            // Suivi interne
            $table->text('notes_personnelles')->nullable();
            $table->text('notes_administratives')->nullable();
    
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
