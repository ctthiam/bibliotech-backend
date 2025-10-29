<?php
// ============================================
// app/Console/Commands/EnvoyerNotifications.php
// Créez ce fichier avec: php artisan make:command EnvoyerNotifications
// ============================================
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;

class EnvoyerNotifications extends Command
{
    protected $signature = 'notifications:envoyer';
    protected $description = 'Envoie les notifications automatiques (rappels et alertes)';

    public function handle()
    {
        $this->info('Envoi des notifications automatiques...');

        // Envoyer les rappels
        $this->info('📨 Envoi des rappels de retour...');
        $rappels = NotificationController::envoyerRappelsAutomatiques();
        $this->info("✓ {$rappels['rappels_envoyes']} rappel(s) envoyé(s)");

        // Envoyer les alertes de retard
        $this->info('⚠️  Envoi des alertes de retard...');
        $alertes = NotificationController::envoyerAlertesRetard();
        $this->info("✓ {$alertes['alertes_envoyees']} alerte(s) envoyée(s)");

        $this->info('✅ Notifications envoyées avec succès !');

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
    
    // Envoie les notifications chaque jour à 9h
    $schedule->command('notifications:envoyer')->dailyAt('09:00');
}
*/