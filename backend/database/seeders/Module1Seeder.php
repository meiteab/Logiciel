<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Module1Seeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('👥 Module1Seeder: élèves, parents, personnels (idempotent)...');

        // 1) Personnel (enseignant) - Structure simplifiée
        DB::table('personnels')->updateOrInsert(
            ['matricule' => 'P001'],
            [
                'prenom' => 'Marie',
                'nom_famille' => 'Dubois',
                'sexe' => 'F',
                'date_naissance' => '1985-05-15',
                'lieu_naissance' => 'Paris',
                'telephone' => '0123456789',
                'email_professionnel' => 'marie.dubois@ecole.fr',
                'adresse' => '123 Rue de l\'École',
                'date_embauche' => '2020-09-01',
                'type_personnel' => 'enseignant',
                'fonction' => 'Enseignante de français',
                'departement' => 'Langues',
                'specialite' => 'Français langue étrangère',
                'est_actif' => 1,
            ]
        );
        $personnelId = DB::table('personnels')->where('matricule','P001')->value('id');

        // 2) Élève
        DB::table('eleves')->updateOrInsert(
            ['matricule' => 'E001'],
            [
                'prenom' => 'Ahmed',
                'nom_famille' => 'Ben Ali',
                'sexe' => 'M',
                'date_naissance' => '2018-03-15',
                'lieu_naissance' => 'Paris',
                'infos_sante' => 'Aucun problème de santé',
                'observations_pedagogiques' => 'Élève motivé, bon niveau en français et arabe',
            ]
        );
        $eleveId = DB::table('eleves')->where('matricule','E001')->value('id');

        // 3) Parents (pas de matricule dans la table)
        DB::table('parents')->updateOrInsert(
            ['prenom' => 'Fatima', 'nom_famille' => 'Ben Ali'],
            [
                'civilite' => 'Mme',
                'date_naissance' => '1985-07-20',
                'lieu_naissance' => 'Casablanca',
                'profession' => 'Médecin',
                'est_actif' => 1,
                'telephone' => '0987654321',
                'telephone_urgence' => '0987654321',
                'email' => 'fatima.benali@email.com',
                'adresse' => '123 Rue de l\'École',
                'ville' => 'Paris',
                'code_postal' => '75001',
                'pays' => 'France',
            ]
        );
        $parentId = DB::table('parents')->where('prenom','Fatima')->where('nom_famille','Ben Ali')->value('id');

        // 4) Relation élève-parent (colonnes: role, est_responsable_legal, autorisations, ordre_priorite)
        if ($eleveId && $parentId) {
            DB::table('eleves_parents')->updateOrInsert(
                ['eleve_id' => $eleveId, 'parent_id' => $parentId, 'role' => 'mere'],
                [
                    'est_responsable_legal' => 1,
                    'autorisations' => json_encode(['acces_notes', 'communication', 'sortie_autorisee']),
                    'ordre_priorite' => 1,
                ]
            );
        }

        $this->command?->info('✅ Module1Seeder terminé.');
    }
} 