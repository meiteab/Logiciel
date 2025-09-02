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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Lien optionnel vers user
    $table->enum('civilite', ['M', 'Mme', 'Mlle'])->nullable();
            $table->string('prenom'); // Rendre obligatoire
            $table->string('nom_famille'); // Rendre obligatoire
    $table->date('date_naissance')->nullable();
    $table->string('lieu_naissance')->nullable();
    $table->string('telephone')->nullable();
    $table->string('telephone_urgence')->nullable();
    $table->string('email')->nullable();
    $table->string('adresse')->nullable();
    $table->string('ville')->nullable();
    $table->string('code_postal')->nullable();
            $table->string('pays')->nullable();
    $table->string('profession')->nullable();
            $table->string('employeur')->nullable(); // Ajouter employeur
            $table->string('telephone_bureau')->nullable(); // Ajouter téléphone bureau
    $table->boolean('est_actif')->default(true);
    $table->timestamps();
            $table->softDeletes(); // Ajouter softDeletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
