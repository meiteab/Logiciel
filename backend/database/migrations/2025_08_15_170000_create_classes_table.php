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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 50); // Ex: "CP-A", "CE1-B", "6ème-A"
            $table->string('code', 20)->unique(); // Ex: "CP-A-2024", "CE1-B-2024"
            $table->integer('capacite_max')->default(30);
            $table->integer('capacite_actuelle')->default(0);
            $table->enum('statut', ['active', 'inactive', 'archivee'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index('code');
            $table->index('statut');
            $table->index(['capacite_max', 'capacite_actuelle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
}; 