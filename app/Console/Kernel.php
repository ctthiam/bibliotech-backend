<?php
// ============================================
// app/Console/Kernel.php
// ============================================
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ============================================
        // 🔥 TÂCHES AUTOMATIQUES BIBLIOTECH
        // ============================================
        
        // 1️⃣ Calcul automatique des pénalités chaque jour à minuit
        $schedule->command('penalites:calculer')
            ->daily()
            ->at('00:00')
            ->appendOutputTo(storage_path('logs/penalites.log'));
        
        // 2️⃣ Envoi des notifications (rappels et alertes) chaque jour à 9h
        $schedule->command('notifications:envoyer')
            ->dailyAt('09:00')
            ->appendOutputTo(storage_path('logs/notifications.log'));
        
        // 3️⃣ BONUS : Nettoyage des notifications anciennes (tous les dimanches à 2h)
        $schedule->call(function () {
            // Supprimer les notifications lues de plus de 30 jours
            \App\Models\Notification::where('lu', true)
                ->where('date_lecture', '<', now()->subDays(30))
                ->delete();
        })
        ->weekly()
        ->sundays()
        ->at('02:00')
        ->appendOutputTo(storage_path('logs/cleanup.log'));
        
        // 4️⃣ BONUS : Marquer les réservations expirées (tous les jours à 23h)
        $schedule->call(function () {
            // Réservations disponibles depuis plus de 48h
            \App\Models\Reservation::where('statut', 'disponible')
                ->where('updated_at', '<', now()->subHours(48))
                ->update(['statut' => 'expiree']);
        })
        ->daily()
        ->at('23:00')
        ->appendOutputTo(storage_path('logs/reservations.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}