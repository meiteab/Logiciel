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
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();
            
            // Informations de l'annonce
            $table->string('titre'); // Titre de l'annonce
            $table->text('contenu'); // Contenu de l'annonce
            $table->enum('type_annonce', ['evenement', 'reunion', 'examen', 'information', 'urgence', 'autre'])->default('information');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->enum('statut', ['brouillon', 'publiee', 'archived', 'annulee'])->default('brouillon');
            
            // Cibles de l'annonce
            $table->json('destinataires_personnels')->nullable(); // IDs des personnels destinataires
            $table->json('destinataires_parents')->nullable(); // IDs des parents destinataires
            $table->json('destinataires_eleves')->nullable(); // IDs des élèves destinataires
            $table->json('destinataires_classes')->nullable(); // IDs des classes destinataires
            $table->json('destinataires_niveaux')->nullable(); // IDs des niveaux destinataires
            $table->boolean('destinataires_tous')->default(false); // Destinataires : tous les utilisateurs
            
            // Dates et événements
            $table->date('date_debut')->nullable(); // Date de début de l'événement
            $table->date('date_fin')->nullable(); // Date de fin de l'événement
            $table->time('heure_debut')->nullable(); // Heure de début
            $table->time('heure_fin')->nullable(); // Heure de fin
            $table->string('lieu')->nullable(); // Lieu de l'événement
            
            // Publication
            $table->foreignId('auteur_id')->constrained('personnels')->onDelete('cascade'); // Auteur de l'annonce
            $table->foreignId('valide_par_id')->nullable()->constrained('personnels')->onDelete('set null'); // Validation par
            $table->timestamp('date_publication')->nullable(); // Date de publication
            $table->timestamp('date_validation')->nullable(); // Date de validation
            $table->timestamp('date_archivage')->nullable(); // Date d'archivage
            
            // Notifications
            $table->boolean('notification_email_envoyee')->default(false);
            $table->boolean('notification_sms_envoyee')->default(false);
            $table->boolean('notification_push_envoyee')->default(false);
            $table->timestamp('date_notification_email')->nullable();
            $table->timestamp('date_notification_sms')->nullable();
            $table->timestamp('date_notification_push')->nullable();
            
            // Pièces jointes et liens
            $table->json('pieces_jointes')->nullable(); // Fichiers attachés
            $table->string('lien_externe')->nullable(); // Lien externe
            $table->string('image_illustration')->nullable(); // Image d'illustration
            
            // Paramètres d'affichage
            $table->boolean('afficher_portail_eleves')->default(false); // Afficher sur le portail élèves
            $table->boolean('afficher_portail_parents')->default(false); // Afficher sur le portail parents
            $table->boolean('afficher_portail_enseignants')->default(false); // Afficher sur le portail enseignants
            $table->boolean('afficher_ecran_accueil')->default(false); // Afficher sur l'écran d'accueil
            $table->boolean('afficher_bandeau_urgent')->default(false); // Afficher en bandeau urgent
            
            // Suivi et statistiques
            $table->integer('nombre_vues')->default(0); // Nombre de vues
            $table->integer('nombre_confirmations')->default(0); // Nombre de confirmations de présence
            $table->json('statistiques_vues')->nullable(); // Statistiques de vues par type d'utilisateur
            
            // Commentaires et réactions
            $table->boolean('autorise_commentaires')->default(false); // Autorise les commentaires
            $table->boolean('autorise_reactions')->default(false); // Autorise les réactions (like, etc.)
            $table->text('commentaires_moderation')->nullable(); // Commentaires de modération
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['auteur_id', 'date_publication']);
            $table->index(['type_annonce', 'statut']);
            $table->index(['priorite', 'date_publication']);
            $table->index('statut');
            $table->index('date_publication');
            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('valide_par_id');
            $table->index('notification_email_envoyee');
            $table->index('notification_sms_envoyee');
            $table->index('notification_push_envoyee');
            $table->index('afficher_portail_eleves');
            $table->index('afficher_portail_parents');
            $table->index('afficher_portail_enseignants');
            $table->index('afficher_ecran_accueil');
            $table->index('afficher_bandeau_urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
