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
        Schema::create('canaux_notification', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: EMAIL, SMS, PUSH, WHATSAPP, TELEGRAM
            $table->string('nom'); // Ex: "Email", "SMS", "Notification Push"
            $table->text('description')->nullable(); // Description du canal
            $table->enum('type', ['email', 'sms', 'push', 'messagerie', 'autre'])->default('email');
            $table->boolean('est_en_ligne')->default(true); // Canal en ligne ou hors ligne
            $table->boolean('est_immediat')->default(true); // Notification immédiate ou différée
            $table->integer('delai_envoi_minutes')->default(0); // Délai d'envoi en minutes
            $table->decimal('cout_unitaire', 8, 4)->default(0.0000); // Coût par notification
            $table->string('devise')->default('EUR'); // Devise du coût
            $table->text('parametres_configuration')->nullable(); // Paramètres de configuration JSON
            $table->text('instructions_utilisation')->nullable(); // Instructions d'utilisation
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Canal actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['type', 'est_en_ligne']);
            $table->index('est_immediat');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canaux_notification');
    }
}; 