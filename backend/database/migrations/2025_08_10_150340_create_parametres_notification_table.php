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
        Schema::create('parametres_notification', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: NOTIF_ABSENCE, NOTIF_RETARD, NOTIF_NOTE, NOTIF_PAIEMENT
            $table->string('nom'); // Ex: "Notification d'absence", "Notification de retard"
            $table->text('description')->nullable(); // Description du paramètre
            $table->foreignId('modele_notification_id')->constrained('modeles_notification')->onDelete('cascade');
            $table->foreignId('canal_notification_id')->constrained('canaux_notification')->onDelete('cascade');
            $table->foreignId('profil_id')->nullable()->constrained('profils')->onDelete('set null');
            $table->boolean('est_actif')->default(true); // Paramètre actif ou non
            $table->boolean('est_obligatoire')->default(false); // Notification obligatoire ou optionnelle
            $table->integer('delai_envoi_minutes')->default(0); // Délai d'envoi en minutes
            $table->enum('frequence', ['immediat', 'quotidien', 'hebdomadaire', 'mensuel', 'personnalise'])->default('immediat');
            $table->json('conditions_envoi')->nullable(); // Conditions d'envoi (JSON)
            $table->json('horaires_envoi')->nullable(); // Horaires autorisés pour l'envoi (JSON)
            $table->text('notes')->nullable(); // Notes spéciales
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['modele_notification_id', 'canal_notification_id'], 'idx_param_notif_modele_canal');
            $table->index(['profil_id', 'est_actif'], 'idx_param_notif_profil_actif');
            $table->index('frequence', 'idx_param_notif_frequence');
            $table->index('est_actif', 'idx_param_notif_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_notification');
    }
}; 