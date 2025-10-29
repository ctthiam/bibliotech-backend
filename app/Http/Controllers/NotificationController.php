<?php
// ============================================
// app/Http/Controllers/NotificationController.php
// ============================================
namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Notification::where('destinataire_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('lu')) {
            $lu = filter_var($request->lu, FILTER_VALIDATE_BOOLEAN);
            $query->where('lu', $lu);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'message' => 'Liste des notifications récupérée'
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerLue(Request $request, $id)
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('destinataire_id', $user->id)
            ->firstOrFail();

        $notification->lu = true;
        $notification->date_lecture = now();
        $notification->save();

        return response()->json([
            'success' => true,
            'data' => $notification,
            'message' => 'Notification marquée comme lue'
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function marquerToutesLues(Request $request)
    {
        $user = $request->user();
        
        Notification::where('destinataire_id', $user->id)
            ->where('lu', false)
            ->update([
                'lu' => true,
                'date_lecture' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $notification = Notification::where('id', $id)
            ->where('destinataire_id', $user->id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée'
        ]);
    }

    /**
     * Nombre de notifications non lues
     */
    public function nonLues(Request $request)
    {
        $user = $request->user();
        
        $count = Notification::where('destinataire_id', $user->id)
            ->where('lu', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
            'message' => 'Nombre de notifications non lues'
        ]);
    }

    /**
     * MÉTHODES STATIQUES POUR CRÉER DES NOTIFICATIONS
     */

    /**
     * Notification de rappel avant date de retour
     */
    public static function envoyerRappelRetour($emprunt)
    {
        $notification = Notification::create([
            'destinataire_id' => $emprunt->lecteur->user_id,
            'type' => 'rappel',
            'titre' => 'Rappel de retour',
            'contenu' => sprintf(
                'N\'oubliez pas de retourner le livre "%s" avant le %s pour éviter les pénalités.',
                $emprunt->exemplaire->livre->titre,
                $emprunt->date_retour_prevue->format('d/m/Y')
            ),
            'donnees' => json_encode([
                'emprunt_id' => $emprunt->id,
                'livre_id' => $emprunt->exemplaire->livre_id,
                'date_retour' => $emprunt->date_retour_prevue->format('Y-m-d')
            ])
        ]);

        // Envoyer email si activé
        self::envoyerEmail($notification);

        return $notification;
    }

    /**
     * Notification de retard
     */
    public static function envoyerAlerteRetard($emprunt)
    {
        $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
        
        $notification = Notification::create([
            'destinataire_id' => $emprunt->lecteur->user_id,
            'type' => 'retard',
            'titre' => '⚠️ Retard de retour',
            'contenu' => sprintf(
                'Vous avez %d jour(s) de retard pour le livre "%s". Des pénalités s\'appliquent : %d FCFA.',
                $joursRetard,
                $emprunt->exemplaire->livre->titre,
                $joursRetard * 100
            ),
            'donnees' => json_encode([
                'emprunt_id' => $emprunt->id,
                'livre_id' => $emprunt->exemplaire->livre_id,
                'jours_retard' => $joursRetard,
                'montant_penalite' => $joursRetard * 100
            ])
        ]);

        self::envoyerEmail($notification);

        return $notification;
    }

    /**
     * Notification de réservation disponible
     */
    public static function envoyerDisponibiliteReservation($reservation)
    {
        $notification = Notification::create([
            'destinataire_id' => $reservation->lecteur->user_id,
            'type' => 'disponibilite',
            'titre' => '🎉 Livre disponible !',
            'contenu' => sprintf(
                'Le livre "%s" que vous aviez réservé est maintenant disponible ! Passez le récupérer dans les 48 heures.',
                $reservation->livre->titre
            ),
            'donnees' => json_encode([
                'reservation_id' => $reservation->id,
                'livre_id' => $reservation->livre_id,
                'date_limite' => now()->addDays(2)->format('Y-m-d')
            ])
        ]);

        self::envoyerEmail($notification);

        return $notification;
    }

    /**
     * Notification d'emprunt confirmé
     */
    public static function envoyerConfirmationEmprunt($emprunt)
    {
        $notification = Notification::create([
            'destinataire_id' => $emprunt->lecteur->user_id,
            'type' => 'information',
            'titre' => '✅ Emprunt confirmé',
            'contenu' => sprintf(
                'Votre emprunt du livre "%s" est confirmé. Date de retour prévue : %s.',
                $emprunt->exemplaire->livre->titre,
                $emprunt->date_retour_prevue->format('d/m/Y')
            ),
            'donnees' => json_encode([
                'emprunt_id' => $emprunt->id,
                'livre_id' => $emprunt->exemplaire->livre_id,
                'date_retour' => $emprunt->date_retour_prevue->format('Y-m-d')
            ])
        ]);

        return $notification;
    }

    /**
     * Notification de retour confirmé
     */
    public static function envoyerConfirmationRetour($emprunt)
    {
        $notification = Notification::create([
            'destinataire_id' => $emprunt->lecteur->user_id,
            'type' => 'information',
            'titre' => '👍 Retour confirmé',
            'contenu' => sprintf(
                'Le retour du livre "%s" a été enregistré. Merci d\'avoir respecté les délais !',
                $emprunt->exemplaire->livre->titre
            ),
            'donnees' => json_encode([
                'emprunt_id' => $emprunt->id,
                'livre_id' => $emprunt->exemplaire->livre_id
            ])
        ]);

        return $notification;
    }

    /**
     * Envoyer l'email si configuré
     */
    private static function envoyerEmail($notification)
    {
        try {
            $user = User::find($notification->destinataire_id);
            
            if ($user && $user->email) {
                // TODO: Configurer l'envoi d'email
                // Mail::to($user->email)->send(new NotificationMail($notification));
            }
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email notification: ' . $e->getMessage());
        }
    }

    /**
     * COMMANDE CRON : Envoyer les rappels automatiques
     */
    public static function envoyerRappelsAutomatiques()
    {
        // Rappels 3 jours avant la date de retour
        $empruntsARappeler = \App\Models\Emprunt::where('statut', 'en_cours')
            ->whereDate('date_retour_prevue', '=', now()->addDays(3)->toDateString())
            ->with('lecteur.user', 'exemplaire.livre')
            ->get();

        $compteur = 0;
        foreach ($empruntsARappeler as $emprunt) {
            // Vérifier si un rappel n'a pas déjà été envoyé
            $dejaEnvoye = Notification::where('destinataire_id', $emprunt->lecteur->user_id)
                ->where('type', 'rappel')
                ->where('donnees->emprunt_id', $emprunt->id)
                ->whereDate('created_at', '>=', now()->subDays(3))
                ->exists();

            if (!$dejaEnvoye) {
                self::envoyerRappelRetour($emprunt);
                $compteur++;
            }
        }

        return [
            'rappels_envoyes' => $compteur,
            'total_emprunts' => $empruntsARappeler->count()
        ];
    }

    /**
     * COMMANDE CRON : Envoyer les alertes de retard
     */
    public static function envoyerAlertesRetard()
    {
        $empruntsEnRetard = \App\Models\Emprunt::where('statut', 'en_retard')
            ->with('lecteur.user', 'exemplaire.livre')
            ->get();

        $compteur = 0;
        foreach ($empruntsEnRetard as $emprunt) {
            // Envoyer une alerte tous les 3 jours
            $derniereAlerte = Notification::where('destinataire_id', $emprunt->lecteur->user_id)
                ->where('type', 'retard')
                ->where('donnees->emprunt_id', $emprunt->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $doitEnvoyer = !$derniereAlerte || 
                          $derniereAlerte->created_at->diffInDays(now()) >= 3;

            if ($doitEnvoyer) {
                self::envoyerAlerteRetard($emprunt);
                $compteur++;
            }
        }

        return [
            'alertes_envoyees' => $compteur,
            'total_retards' => $empruntsEnRetard->count()
        ];
    }
}