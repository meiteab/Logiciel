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
        Schema::table('users', function (Blueprint $table) {
            // Informations personnelles
            $table->string('prenom')->nullable()->after('name'); // Prénom
            $table->string('nom_famille')->nullable()->after('prenom'); // Nom de famille
            $table->enum('civilite', ['M', 'Mme', 'Mlle'])->nullable()->after('nom_famille'); // Civilité
            $table->date('date_naissance')->nullable()->after('civilite'); // Date de naissance
            $table->string('lieu_naissance')->nullable()->after('date_naissance'); // Lieu de naissance
            $table->enum('sexe', ['M', 'F'])->nullable()->after('lieu_naissance'); // Sexe
            
            // Informations de contact
            $table->string('telephone')->nullable()->after('sexe'); // Téléphone
            $table->string('telephone_urgence')->nullable()->after('telephone'); // Téléphone d'urgence
            $table->string('adresse')->nullable()->after('telephone_urgence'); // Adresse complète
            $table->string('ville')->nullable()->after('adresse'); // Ville
            $table->string('code_postal')->nullable()->after('ville'); // Code postal
            
            
            // Informations professionnelles
            $table->string('matricule')->nullable()->unique()->after('pays'); // Matricule unique
            $table->date('date_embauche')->nullable()->after('matricule'); // Date d'embauche
            $table->date('date_depart')->nullable()->after('date_embauche'); // Date de départ
            $table->enum('statut', ['actif', 'inactif', 'suspendu', 'retraite'])->default('actif')->after('date_depart');
            
            // Informations scolaires
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('set null')->after('statut');
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->onDelete('set null')->after('niveau_id');
            $table->string('classe')->nullable()->after('programme_id'); // Classe actuelle
            
            // Informations de sécurité
            $table->boolean('est_premiere_connexion')->default(true)->after('classe'); // Première connexion
            $table->timestamp('derniere_connexion')->nullable()->after('est_premiere_connexion'); // Dernière connexion
            $table->integer('nombre_tentatives_connexion')->default(0)->after('derniere_connexion'); // Tentatives de connexion
            $table->timestamp('verrouillage_until')->nullable()->after('nombre_tentatives_connexion'); // Verrouillage temporaire
            
            // Informations de préférences
            $table->string('langue_preferee')->default('fr')->after('verrouillage_until'); // Langue préférée
            $table->string('fuseau_horaire')->default('Africa/Abidjan')->after('langue_preferee'); // Fuseau horaire
            $table->json('preferences_notification')->nullable()->after('fuseau_horaire'); // Préférences de notification
            
            // Informations de suivi
            $table->text('notes_personnelles')->nullable()->after('preferences_notification'); // Notes personnelles
            $table->text('notes_administratives')->nullable()->after('notes_personnelles'); // Notes administratives
            $table->boolean('est_actif')->default(true)->after('notes_administratives'); // Utilisateur actif ou non
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Suppression des colonnes ajoutées
            $table->dropForeign(['niveau_id', 'programme_id']);
            $table->dropColumn([
                'prenom', 'nom_famille', 'civilite', 'date_naissance', 'lieu_naissance', 'sexe',
                'telephone', 'telephone_urgence', 'adresse', 'ville', 'code_postal', 'pays',
                'matricule', 'date_embauche', 'date_depart', 'statut', 'niveau_id', 'programme_id',
                'classe', 'est_premiere_connexion', 'derniere_connexion', 'nombre_tentatives_connexion',
                'verrouillage_until', 'langue_preferee', 'fuseau_horaire', 'preferences_notification',
                'notes_personnelles', 'notes_administratives', 'est_actif'
            ]);
        });
    }
}; 