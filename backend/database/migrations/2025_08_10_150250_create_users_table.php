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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profil_id')->nullable()->constrained('profils')->onDelete('set null');
            $table->string('identifiant')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->boolean('two_factor_enabled')->default(false);
            $table->enum('statut', ['actif', 'inactif', 'verrouille', 'supprime'])->default('actif');
            $table->timestamp('derniere_connexion')->nullable()->index();
            $table->integer('tentatives_connexion')->default(0);
            $table->timestamp('verrouille_jusqua')->nullable();
            $table->string('langue', 5)->default('fr');
            $table->string('fuseau_horaire')->default('Africa/Abidjan');
            $table->timestamps();
            $table->softDeletes();
        });        
        
        
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
