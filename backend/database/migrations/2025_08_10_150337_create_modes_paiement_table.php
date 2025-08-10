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
        Schema::create('modes_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: ESPECES, CHEQUE, VIREMENT, CARTE, MOBILE
            $table->string('nom'); // Ex: "Espèces", "Chèque", "Virement bancaire"
            $table->text('description')->nullable(); // Description du mode de paiement
            $table->enum('categorie', ['especes', 'cheque', 'virement', 'carte', 'mobile', 'autre'])->default('autre');
            $table->boolean('est_en_ligne')->default(false); // Paiement en ligne ou physique
            $table->boolean('est_immediat')->default(true); // Paiement immédiat ou différé
            $table->integer('delai_traitement_jours')->default(0); // Délai de traitement en jours
            $table->decimal('frais_transaction', 8, 2)->default(0.00); // Frais de transaction
            $table->decimal('frais_transaction_pourcentage', 5, 2)->default(0.00); // Frais en pourcentage
            $table->text('instructions')->nullable(); // Instructions pour le paiement
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Mode de paiement actif ou non
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['categorie', 'est_en_ligne']);
            $table->index('est_immediat');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modes_paiement');
    }
}; 