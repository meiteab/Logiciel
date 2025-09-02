<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 DatabaseSeeder: seeding BaseData, Module1, Module2, Module8, Module3, Module5, Module6, Inscriptions, TablesManquantes');
        $this->call(BaseDataSeeder::class);
        $this->call(Module1Seeder::class);
        $this->call(Module2Seeder::class);
        $this->call(Module8Seeder::class);
        $this->call(Module3Seeder::class);
        $this->call(Module5Seeder::class);
        $this->call(Module6Seeder::class);
        $this->call(InscriptionsSeeder::class);
        $this->call(TablesManquantesSeeder::class);
    }
}
