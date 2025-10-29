<?php
// ============================================
// app/Console/Commands/CalculerPenalites.php
// Créez ce fichier avec: php artisan make:command CalculerPenalites
// ============================================
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Penalite;
use App\Models\Emprunt;

class CalculerPenalites extends Command
{
    protected $signature = 'penalites:calculer';
    protected $description = 'Calcule et met à jour les pénalités pour les emprunts en retard';

    public function handle()
    {
        $this->info('Calcul des pénalités en cours...');

        $empruntsEnRetard = Emprunt::where('statut', 'en_cours')
            ->whereDate('date_retour_prevue', '<', now())
            ->get();

        $penalitesCreees = 0;
        $penalitesMisesAJour = 0;

        foreach ($empruntsEnRetard as $emprunt) {
            $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
            $montant = Penalite::calculerMontant($joursRetard);

            // Vérifier si une pénalité existe déjà
            $penalite = Penalite::where('emprunt_id', $emprunt->id)
                ->where('statut', 'impayee')
                ->first();

            if ($penalite) {
                // Mettre à jour le montant
                $penalite->montant = $montant;
                $penalite->motif = "Retard de {$joursRetard} jour(s)";
                $penalite->save();
                $penalitesMisesAJour++;
            } else {
                // Créer une nouvelle pénalité
                Penalite::create([
                    'lecteur_id' => $emprunt->lecteur_id,
                    'emprunt_id' => $emprunt->id,
                    'montant' => $montant,
                    'motif' => "Retard de {$joursRetard} jour(s)",
                    'statut' => 'impayee',
                ]);
                $penalitesCreees++;
            }

            // Mettre à jour le statut de l'emprunt
            if ($emprunt->statut !== 'en_retard') {
                $emprunt->statut = 'en_retard';
                $emprunt->save();
            }
        }

        $this->info("✓ {$penalitesCreees} pénalité(s) créée(s)");
        $this->info("✓ {$penalitesMisesAJour} pénalité(s) mise(s) à jour");
        $this->info("✓ {$empruntsEnRetard->count()} emprunt(s) en retard traité(s)");

        return 0;
    }
}

// ============================================
// PUIS, ajoutez dans app/Console/Kernel.php
// Dans la méthode schedule() :
// ============================================
/*
protected function schedule(Schedule $schedule)
{
    // Calcule les pénalités chaque jour à minuit
    $schedule->command('penalites:calculer')->daily();
}
*/