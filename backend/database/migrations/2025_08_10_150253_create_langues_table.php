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
        Schema::create('langues', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ex: FR, AR, EN, ES
            $table->string('nom'); // Ex: "Français", "Arabe", "Anglais"
            $table->string('nom_natif')->nullable(); // Nom dans la langue native
            $table->text('description')->nullable(); // Description de la langue
            $table->enum('categorie', ['officielle', 'regionale', 'etrangere', 'autre'])->default('officielle');
            $table->string('code_iso_639_1', 2)->nullable(); // Code ISO 639-1 (ex: fr, ar, en)
            $table->string('code_iso_639_2', 3)->nullable(); // Code ISO 639-2 (ex: fra, ara, eng)
            $table->enum('direction_ecriture', ['ltr', 'rtl'])->default('ltr'); // Direction d'écriture
            $table->boolean('est_langue_principale')->default(false); // Langue principale de l'école
            $table->boolean('est_langue_enseignement')->default(false); // Langue d'enseignement
            $table->boolean('est_langue_obligatoire')->default(false); // Langue obligatoire
            $table->integer('niveau_requis')->nullable(); // Niveau requis pour cette langue
            $table->text('notes')->nullable(); // Notes spéciales
            $table->boolean('est_actif')->default(true); // Langue active ou non
            $table->timestamps();
            $table->index(['categorie', 'direction_ecriture']);
            $table->index('est_langue_principale');
            $table->index('est_langue_enseignement');
            $table->index('est_actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('langues');
    }
};
