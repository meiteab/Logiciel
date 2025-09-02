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
        Schema::create('eleves_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->enum('role', ['pere', 'mere', 'tuteur'])->default('tuteur'); // Rôle du parent
            $table->boolean('est_responsable_legal')->default(false); // Ajouter responsable légal
            $table->json('autorisations')->nullable(); // Autorisations spéciales (ex: accès notes, communication)
            $table->integer('ordre_priorite')->default(1); // Ajouter ordre de priorité
            $table->timestamps();

            $table->unique(['eleve_id', 'parent_id', 'role'], 'uk_eleve_parent_role'); // Améliorer la contrainte unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eleves_parents');
    }
};
