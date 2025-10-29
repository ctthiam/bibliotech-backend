<?php
// ============================================
// app/Http/Controllers/ReservationController.php
// ============================================
namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Livre;
use App\Models\Exemplaire;
use App\Models\Lecteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Liste des réservations
     * Admin voit tout, Lecteur voit ses réservations
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Reservation::with(['lecteur.user', 'livre.exemplaires'])
            ->orderBy('created_at', 'desc');

        // Si lecteur, filtrer par ses réservations
        if ($user->role === 'lecteur' && $user->lecteur) {
            $query->where('lecteur_id', $user->lecteur->id);
        }

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('livre_id')) {
            $query->where('livre_id', $request->livre_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reservations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reservations,
            'message' => 'Liste des réservations récupérée'
        ]);
    }

    /**
     * Créer une réservation
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Vérifier que c'est un lecteur
        if ($user->role !== 'lecteur' || !$user->lecteur) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les lecteurs peuvent réserver des livres'
            ], 403);
        }

        $request->validate([
            'livre_id' => 'required|exists:livres,id'
        ]);

        $livre = Livre::findOrFail($request->livre_id);
        $lecteur = $user->lecteur;

        // Vérifier si le livre a des exemplaires disponibles
        $exemplairesDisponibles = Exemplaire::where('livre_id', $livre->id)
            ->where('statut', 'disponible')
            ->count();

        if ($exemplairesDisponibles > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Ce livre est disponible, vous pouvez l\'emprunter directement'
            ], 400);
        }

        // Vérifier si le lecteur n'a pas déjà réservé ce livre
        $reservationExistante = Reservation::where('lecteur_id', $lecteur->id)
            ->where('livre_id', $livre->id)
            ->where('statut', 'en_attente')
            ->first();

        if ($reservationExistante) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà réservé ce livre'
            ], 400);
        }

        // Créer la réservation
        $reservation = Reservation::create([
            'lecteur_id' => $lecteur->id,
            'livre_id' => $livre->id,
            'statut' => 'en_attente'
        ]);

        return response()->json([
            'success' => true,
            'data' => $reservation->load('livre', 'lecteur.user'),
            'message' => 'Réservation créée avec succès'
        ], 201);
    }

    /**
     * Détails d'une réservation
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = Reservation::with(['lecteur.user', 'livre.exemplaires'])
            ->findOrFail($id);

        // Vérifier les permissions
        if ($user->role === 'lecteur' && $reservation->lecteur_id !== $user->lecteur->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => 'Détails de la réservation'
        ]);
    }

    /**
     * Annuler une réservation
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        
        $reservation = Reservation::findOrFail($id);

        // Vérifier les permissions
        if ($user->role === 'lecteur' && $reservation->lecteur_id !== $user->lecteur->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        if ($reservation->statut === 'annulee') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation est déjà annulée'
            ], 400);
        }

        $reservation->statut = 'annulee';
        $reservation->save();

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => 'Réservation annulée avec succès'
        ]);
    }

    /**
     * Marquer comme disponible (Admin/Bibliothécaire)
     * Appelé automatiquement quand un exemplaire du livre devient disponible
     */
    public function marquerDisponible(Request $request, $id)
    {
        $user = $request->user();

        // Vérifier les permissions
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $reservation = Reservation::findOrFail($id);

        if ($reservation->statut !== 'en_attente') {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation n\'est pas en attente'
            ], 400);
        }

        $reservation->statut = 'disponible';
        $reservation->save();

        // TODO: Envoyer une notification au lecteur

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => 'Réservation marquée comme disponible'
        ]);
    }

    /**
     * Marquer comme expirée (Admin/Bibliothécaire)
     */
    public function marquerExpiree(Request $request, $id)
    {
        $user = $request->user();

        // Vérifier les permissions
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $reservation = Reservation::findOrFail($id);

        $reservation->statut = 'expiree';
        $reservation->save();

        return response()->json([
            'success' => true,
            'data' => $reservation,
            'message' => 'Réservation marquée comme expirée'
        ]);
    }

    /**
     * Statistiques des réservations
     */
    public function statistiques(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'lecteur' && $user->lecteur) {
            $stats = $this->getStatistiquesLecteur($user->lecteur->id);
        } else {
            $stats = $this->getStatistiquesGlobales();
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques des réservations'
        ]);
    }

    /**
     * Vérifier et notifier les réservations disponibles
     * À appeler automatiquement lors du retour d'un livre
     */
    public static function verifierDisponibilite($livreId)
    {
        // Vérifier s'il y a des exemplaires disponibles
        $exemplairesDisponibles = Exemplaire::where('livre_id', $livreId)
            ->where('statut', 'disponible')
            ->count();

        if ($exemplairesDisponibles > 0) {
            // Récupérer la première réservation en attente
            $reservation = Reservation::where('livre_id', $livreId)
                ->where('statut', 'en_attente')
                ->orderBy('created_at', 'asc')
                ->first();

            if ($reservation) {
                $reservation->statut = 'disponible';
                $reservation->save();

                // TODO: Envoyer notification au lecteur
                // Notification::envoyerDisponibilite($reservation);
            }
        }
    }

    /**
     * Statistiques personnelles d'un lecteur
     */
    private function getStatistiquesLecteur($lecteurId)
    {
        return [
            'total_reservations' => Reservation::where('lecteur_id', $lecteurId)->count(),
            'reservations_en_attente' => Reservation::where('lecteur_id', $lecteurId)
                ->where('statut', 'en_attente')
                ->count(),
            'reservations_disponibles' => Reservation::where('lecteur_id', $lecteurId)
                ->where('statut', 'disponible')
                ->count(),
            'reservations_expirees' => Reservation::where('lecteur_id', $lecteurId)
                ->where('statut', 'expiree')
                ->count(),
            'reservations_annulees' => Reservation::where('lecteur_id', $lecteurId)
                ->where('statut', 'annulee')
                ->count(),
        ];
    }

    /**
     * Statistiques globales (Admin)
     */
    private function getStatistiquesGlobales()
    {
        return [
            'total_reservations' => Reservation::count(),
            'reservations_en_attente' => Reservation::where('statut', 'en_attente')->count(),
            'reservations_disponibles' => Reservation::where('statut', 'disponible')->count(),
            'reservations_expirees' => Reservation::where('statut', 'expiree')->count(),
            'reservations_annulees' => Reservation::where('statut', 'annulee')->count(),
            'livres_plus_reserves' => $this->getLivresPlusReserves(),
        ];
    }

    /**
     * Livres les plus réservés
     */
    private function getLivresPlusReserves()
    {
        return DB::table('reservations')
            ->join('livres', 'reservations.livre_id', '=', 'livres.id')
            ->select(
                'livres.id',
                'livres.titre',
                'livres.auteur',
                DB::raw('COUNT(reservations.id) as nombre_reservations')
            )
            ->groupBy('livres.id', 'livres.titre', 'livres.auteur')
            ->orderBy('nombre_reservations', 'desc')
            ->limit(10)
            ->get();
    }
}