<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Administrateur;
use App\Models\Bibliothecaire;
use App\Models\Lecteur;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Créer un Administrateur
        $admin = User::create([
            'nom' => 'DIOP',
            'prenom' => 'Administrateur',
            'email' => 'admin@bibliotech.com',
            'password' => Hash::make('password123'),
            'telephone' => '771234567',
            'role' => 'administrateur',
        ]);

        Administrateur::create([
            'user_id' => $admin->id,
            'privileges' => json_encode(['all']),
        ]);

        // Créer un Bibliothécaire
        $biblio = User::create([
            'nom' => 'SALL',
            'prenom' => 'Bibliothécaire',
            'email' => 'biblio@bibliotech.com',
            'password' => Hash::make('password123'),
            'telephone' => '772345678',
            'role' => 'bibliothecaire',
        ]);

        Bibliothecaire::create([
            'user_id' => $biblio->id,
            'service' => 'Gestion',
            'autorisations' => json_encode(['gerer_catalogue', 'gerer_emprunts']),
        ]);

        echo "✅ Utilisateurs créés :\n";
        echo "Admin: admin@bibliotech.com / password123\n";
        echo "Biblio: biblio@bibliotech.com / password123\n";
    }
}