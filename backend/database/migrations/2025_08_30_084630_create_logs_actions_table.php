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
        Schema::create('logs_actions', function (Blueprint $table) {
            $table->id();
            
            // Informations de l'action
            $table->string('action'); // Nom de l'action (ex: 'create', 'update', 'delete', 'login')
            $table->string('module')->nullable(); // Module concerné (ex: 'eleves', 'notes', 'finances')
            $table->string('table_impactee')->nullable(); // Table impactée
            $table->bigInteger('entite_id')->nullable(); // ID de l'entité concernée
            
            // Utilisateur qui a effectué l'action
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('personnel_id')->nullable()->constrained('personnels')->onDelete('set null');
            $table->string('ip_adresse')->nullable(); // Adresse IP
            $table->string('user_agent')->nullable(); // User agent du navigateur
            
            // Détails de l'action
            $table->enum('niveau', ['info', 'warning', 'error', 'critical'])->default('info');
            $table->text('description')->nullable(); // Description de l'action
            $table->json('donnees_avant')->nullable(); // Données avant modification
            $table->json('donnees_apres')->nullable(); // Données après modification
            $table->json('parametres')->nullable(); // Paramètres de l'action
            
            // Contexte
            $table->string('session_id')->nullable(); // ID de session
            $table->string('url_requete')->nullable(); // URL de la requête
            $table->string('methode_http')->nullable(); // Méthode HTTP (GET, POST, etc.)
            $table->integer('temps_execution_ms')->nullable(); // Temps d'exécution en millisecondes
            
            // Résultat
            $table->enum('statut', ['succes', 'echec', 'erreur'])->default('succes');
            $table->text('message_erreur')->nullable(); // Message d'erreur si applicable
            $table->integer('code_erreur')->nullable(); // Code d'erreur HTTP
            
            // Métadonnées
            $table->string('fuseau_horaire')->nullable(); // Fuseau horaire
            $table->json('metadonnees')->nullable(); // Autres métadonnées
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['user_id', 'created_at']);
            $table->index(['personnel_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index(['table_impactee', 'created_at']);
            $table->index(['niveau', 'created_at']);
            $table->index(['statut', 'created_at']);
            $table->index('created_at');
            $table->index('ip_adresse');
            $table->index('session_id');
            
            // Index pour les requêtes de sécurité
            $table->index(['user_id', 'action', 'created_at']);
            $table->index(['ip_adresse', 'created_at']);
            $table->index(['niveau', 'statut', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_actions');
    }
};
