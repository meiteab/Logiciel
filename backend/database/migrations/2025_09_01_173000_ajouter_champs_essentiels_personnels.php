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
            // Ajouter seulement les champs essentiels pour les enseignants
            if (!Schema::hasColumn('personnels', 'type_personnel')) {
                $table->enum('type_personnel', ['enseignant', 'administratif', 'technique', 'direction', 'autre'])->nullable()->after('date_embauche');
            }
            
            if (!Schema::hasColumn('personnels', 'fonction')) {
                $table->string('fonction')->nullable()->after('type_personnel');
            }
            
            if (!Schema::hasColumn('personnels', 'departement')) {
                $table->string('departement')->nullable()->after('fonction');
            }
            
            if (!Schema::hasColumn('personnels', 'specialite')) {
                $table->string('specialite')->nullable()->after('departement');
            }
            
            if (!Schema::hasColumn('personnels', 'est_actif')) {
                $table->boolean('est_actif')->default(true)->after('specialite');
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
                'type_personnel',
                'fonction',
                'departement',
                'specialite',
                'est_actif'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('personnels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
