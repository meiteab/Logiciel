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
        Schema::table('personnels', function (Blueprint $table) {
            // Ajouter les champs manquants
            if (!Schema::hasColumn('personnels', 'email_personnel')) {
                $table->string('email_personnel')->nullable()->after('email_professionnel');
            }
            
            if (!Schema::hasColumn('personnels', 'diplome')) {
                $table->text('diplome')->nullable()->after('departement');
            }
            
            if (!Schema::hasColumn('personnels', 'numero_securite_sociale')) {
                $table->string('numero_securite_sociale')->nullable()->after('specialite');
            }
            
            if (!Schema::hasColumn('personnels', 'numero_cnss')) {
                $table->string('numero_cnss')->nullable()->after('numero_securite_sociale');
            }
            
            if (!Schema::hasColumn('personnels', 'salaire_brut')) {
                $table->decimal('salaire_brut', 10, 2)->nullable()->after('numero_cnss');
            }
            
            if (!Schema::hasColumn('personnels', 'salaire_net')) {
                $table->decimal('salaire_net', 10, 2)->nullable()->after('salaire_brut');
            }
            
            if (!Schema::hasColumn('personnels', 'banque')) {
                $table->string('banque')->nullable()->after('salaire_net');
            }
            
            if (!Schema::hasColumn('personnels', 'numero_compte')) {
                $table->string('numero_compte')->nullable()->after('banque');
            }
            
            if (!Schema::hasColumn('personnels', 'rib')) {
                $table->string('rib')->nullable()->after('numero_compte');
            }
            
            if (!Schema::hasColumn('personnels', 'motif_depart')) {
                $table->text('motif_depart')->nullable()->after('date_depart');
            }
            
            if (!Schema::hasColumn('personnels', 'photo')) {
                $table->string('photo')->nullable()->after('motif_depart');
            }
            
            if (!Schema::hasColumn('personnels', 'observations')) {
                $table->text('observations')->nullable()->after('photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnels', function (Blueprint $table) {
            $columnsToDrop = [
                'email_personnel',
                'diplome',
                'numero_securite_sociale',
                'numero_cnss',
                'salaire_brut',
                'salaire_net',
                'banque',
                'numero_compte',
                'rib',
                'motif_depart',
                'photo',
                'observations'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('personnels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
