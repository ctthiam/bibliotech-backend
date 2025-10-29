<?php
// ============================================
// app/Http/Controllers/PenaliteController.php
// ============================================
namespace App\Http\Controllers;

use App\Models\Penalite;
use App\Models\Emprunt;
use App\Models\Lecteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenaliteController extends Controller
{
    /**
     * Liste des pénalités (Admin voit tout, Lecteur voit les siennes)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Penalite::with(['lecteur.user', 'emprunt.exemplaire.livre'])
            ->orderBy('created_at', 'desc');

        // Si c'est un lecteur, filtrer par ses pénalités
        if ($user->role === 'lecteur' && $user->lecteur) {
            $query->where('lecteur_id', $user->lecteur->id);
        }

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('lecteur_id') && in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            $query->where('lecteur_id', $request->lecteur_id);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $penalites = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $penalites,
            'message' => 'Liste des pénalités récupérée avec succès'
        ]);
    }

    /**
     * Détails d'une pénalité
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $penalite = Penalite::with(['lecteur.user', 'emprunt.exemplaire.livre'])
            ->findOrFail($id);

        // Vérifier les permissions
        if ($user->role === 'lecteur' && $penalite->lecteur_id !== $user->lecteur->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $penalite,
            'message' => 'Détails de la pénalité'
        ]);
    }

    /**
     * Marquer une pénalité comme payée (Admin/Bibliothécaire)
     */
    public function marquerPayee(Request $request, $id)
    {
        $user = $request->user();

        // Vérifier les permissions
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $penalite = Penalite::findOrFail($id);

        if ($penalite->statut === 'payee') {
            return response()->json([
                'success' => false,
                'message' => 'Cette pénalité est déjà payée'
            ], 400);
        }

        $penalite->marquerCommePayee();

        return response()->json([
            'success' => true,
            'data' => $penalite,
            'message' => 'Pénalité marquée comme payée'
        ]);
    }

    /**
     * Annuler une pénalité (Admin uniquement)
     */
    public function annuler(Request $request, $id)
    {
        $user = $request->user();

        // Seul l'admin peut annuler
        if ($user->role !== 'administrateur') {
            return response()->json([
                'success' => false,
                'message' => 'Seul un administrateur peut annuler une pénalité'
            ], 403);
        }

        $penalite = Penalite::findOrFail($id);

        if ($penalite->statut === 'annulee') {
            return response()->json([
                'success' => false,
                'message' => 'Cette pénalité est déjà annulée'
            ], 400);
        }

        $penalite->statut = 'annulee';
        $penalite->save();

        return response()->json([
            'success' => true,
            'data' => $penalite,
            'message' => 'Pénalité annulée avec succès'
        ]);
    }

    /**
     * Statistiques des pénalités
     */
    public function statistiques(Request $request)
    {
        $user = $request->user();

        // Si lecteur, stats personnelles uniquement
        if ($user->role === 'lecteur' && $user->lecteur) {
            $stats = $this->getStatistiquesLecteur($user->lecteur->id);
        } else {
            $stats = $this->getStatistiquesGlobales();
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Statistiques des pénalités'
        ]);
    }

    /**
     * Calculer et créer les pénalités pour les emprunts en retard
     * (À appeler via une tâche CRON quotidienne)
     */
    public function calculerPenalites()
    {
        $empruntsEnRetard = Emprunt::where('statut', 'en_cours')
            ->whereDate('date_retour_prevue', '<', now())
            ->with('exemplaire.livre', 'lecteur')
            ->get();

        $penalitesCreees = 0;
        $penalitesMisesAJour = 0;

        foreach ($empruntsEnRetard as $emprunt) {
            $joursRetard = now()->diffInDays($emprunt->date_retour_prevue);
            $montant = Penalite::calculerMontant($joursRetard);

            // Vérifier si une pénalité existe déjà pour cet emprunt
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
                    'motif' => "Retard de {$joursRetard} jour(s) - " . $emprunt->exemplaire->livre->titre,
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

        return response()->json([
            'success' => true,
            'data' => [
                'penalites_creees' => $penalitesCreees,
                'penalites_mises_a_jour' => $penalitesMisesAJour,
                'total_emprunts_en_retard' => $empruntsEnRetard->count()
            ],
            'message' => 'Calcul des pénalités effectué'
        ]);
    }

    /**
     * Statistiques personnelles d'un lecteur
     */
    private function getStatistiquesLecteur($lecteurId)
    {
        return [
            'total_penalites' => Penalite::where('lecteur_id', $lecteurId)->count(),
            'penalites_impayees' => Penalite::where('lecteur_id', $lecteurId)
                ->where('statut', 'impayee')
                ->count(),
            'montant_total_impaye' => Penalite::where('lecteur_id', $lecteurId)
                ->where('statut', 'impayee')
                ->sum('montant'),
            'montant_total_paye' => Penalite::where('lecteur_id', $lecteurId)
                ->where('statut', 'payee')
                ->sum('montant'),
            'derniere_penalite' => Penalite::where('lecteur_id', $lecteurId)
                ->latest()
                ->first(),
        ];
    }

    /**
     * Statistiques globales (Admin)
     */
    private function getStatistiquesGlobales()
    {
        return [
            'total_penalites' => Penalite::count(),
            'penalites_impayees' => Penalite::where('statut', 'impayee')->count(),
            'penalites_payees' => Penalite::where('statut', 'payee')->count(),
            'penalites_annulees' => Penalite::where('statut', 'annulee')->count(),
            'montant_total_impaye' => Penalite::where('statut', 'impayee')->sum('montant'),
            'montant_total_paye' => Penalite::where('statut', 'payee')->sum('montant'),
            'montant_total' => Penalite::sum('montant'),
            'top_lecteurs_penalites' => $this->getTopLecteursAvecPenalites(),
        ];
    }

    /**
     * Top lecteurs avec le plus de pénalités
     */
    private function getTopLecteursAvecPenalites()
    {
        return DB::table('penalites')
            ->join('lecteurs', 'penalites.lecteur_id', '=', 'lecteurs.id')
            ->join('users', 'lecteurs.user_id', '=', 'users.id')
            ->select(
                'lecteurs.id',
                'users.nom',
                'users.prenom',
                DB::raw('COUNT(penalites.id) as nombre_penalites'),
                DB::raw('SUM(penalites.montant) as montant_total')
            )
            ->groupBy('lecteurs.id', 'users.nom', 'users.prenom')
            ->orderBy('montant_total', 'desc')
            ->limit(10)
            ->get();
    }
}