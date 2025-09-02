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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Informations du message
            $table->string('sujet'); // Sujet du message
            $table->text('contenu'); // Contenu du message
            $table->enum('type_message', ['interne', 'parent', 'eleve', 'general'])->default('interne');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'urgente'])->default('normale');
            $table->enum('statut', ['brouillon', 'envoye', 'lu', 'archived'])->default('brouillon');
            
            // Expéditeur et destinataires
            $table->foreignId('expediteur_id')->constrained('personnels')->onDelete('cascade'); // Expéditeur (personnel)
            $table->json('destinataires_personnels')->nullable(); // IDs des personnels destinataires
            $table->json('destinataires_parents')->nullable(); // IDs des parents destinataires
            $table->json('destinataires_eleves')->nullable(); // IDs des élèves destinataires
            $table->json('destinataires_groupes')->nullable(); // Groupes destinataires (classes, niveaux, etc.)
            
            // Gestion des réponses
            $table->foreignId('message_parent_id')->nullable()->constrained('messages')->onDelete('cascade'); // Message parent (pour les réponses)
            $table->boolean('autorise_reponse')->default(true); // Autorise les réponses
            $table->boolean('reponse_requise')->default(false); // Réponse requise
            
            // Pièces jointes
            $table->json('pieces_jointes')->nullable(); // Fichiers attachés
            
            // Dates
            $table->timestamp('date_envoi')->nullable(); // Date d'envoi
            $table->timestamp('date_lecture')->nullable(); // Date de première lecture
            $table->timestamp('date_limite_reponse')->nullable(); // Date limite de réponse
            
            // Notifications
            $table->boolean('notification_email_envoyee')->default(false);
            $table->boolean('notification_sms_envoyee')->default(false);
            $table->boolean('notification_push_envoyee')->default(false);
            $table->timestamp('date_notification_email')->nullable();
            $table->timestamp('date_notification_sms')->nullable();
            $table->timestamp('date_notification_push')->nullable();
            
            // Confidentialité et sécurité
            $table->boolean('confidentiel')->default(false); // Message confidentiel
            $table->boolean('signature_requise')->default(false); // Signature requise
            $table->text('notes_confidentielles')->nullable(); // Notes confidentielles
            
            // Suivi
            $table->integer('nombre_lectures')->default(0); // Nombre de lectures
            $table->integer('nombre_reponses')->default(0); // Nombre de réponses
            $table->json('statistiques_lecture')->nullable(); // Statistiques de lecture par destinataire
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index(['expediteur_id', 'date_envoi']);
            $table->index(['type_message', 'statut']);
            $table->index(['priorite', 'date_envoi']);
            $table->index('statut');
            $table->index('date_envoi');
            $table->index('date_lecture');
            $table->index('confidentiel');
            $table->index('message_parent_id');
            $table->index('notification_email_envoyee');
            $table->index('notification_sms_envoyee');
            $table->index('notification_push_envoyee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
