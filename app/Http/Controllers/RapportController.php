<?php
// ============================================
// app/Http/Controllers/RapportController.php
// ============================================
namespace App\Http\Controllers;

use App\Models\Emprunt;
use App\Models\Livre;
use App\Models\Penalite;
use App\Models\Lecteur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use Maatwebsite\Excel\Facades\Excel; // ✅ AJOUTEZ CETTE LIGNE
use App\Exports\InventaireLivresExport; // ✅ AJOUTEZ CETTE LIGNE
use App\Exports\EmpruntsExport; // ✅ BONUS

class RapportController extends Controller
{
    /**
     * RAPPORTS LECTEUR
     */

    /**
     * Mon historique d'emprunts (PDF)
     */
    public function historiqueLecteur(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'lecteur' || !$user->lecteur) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $emprunts = Emprunt::where('lecteur_id', $user->lecteur->id)
            ->with('exemplaire.livre')
            ->orderBy('date_emprunt', 'desc')
            ->get();

        $data = [
            'lecteur' => $user->lecteur->load('user'),
            'emprunts' => $emprunts,
            'date_generation' => now()->format('d/m/Y H:i'),
            'total_emprunts' => $emprunts->count(),
            'en_cours' => $emprunts->where('statut', 'en_cours')->count(),
            'termines' => $emprunts->where('statut', 'termine')->count(),
        ];

        $pdf = PDF::loadView('rapports.historique-lecteur', $data);
        
        return $pdf->download('historique-emprunts-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Mes pénalités (PDF)
     */
    public function penalitesLecteur(Request $request)
    {
        $user = $request->user();
        
        if ($user->role !== 'lecteur' || !$user->lecteur) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $penalites = Penalite::where('lecteur_id', $user->lecteur->id)
            ->with('emprunt.exemplaire.livre')
            ->orderBy('date_creation', 'desc')
            ->get();

        $data = [
            'lecteur' => $user->lecteur->load('user'),
            'penalites' => $penalites,
            'date_generation' => now()->format('d/m/Y H:i'),
            'total_penalites' => $penalites->count(),
            'impayees' => $penalites->where('statut', 'impayee')->count(),
            'montant_total_impaye' => $penalites->where('statut', 'impayee')->sum('montant'),
            'montant_total_paye' => $penalites->where('statut', 'payee')->sum('montant'),
        ];

        $pdf = PDF::loadView('rapports.penalites-lecteur', $data);
        
        return $pdf->download('mes-penalites-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * RAPPORTS ADMIN/BIBLIOTHÉCAIRE
     */

    /**
     * Rapport mensuel des emprunts (PDF)
     */
    public function rapportMensuelEmprunts(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $mois = $request->get('mois', now()->month);
        $annee = $request->get('annee', now()->year);

        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = Carbon::create($annee, $mois, 1)->endOfMonth();

        $emprunts = Emprunt::whereBetween('date_emprunt', [$debut, $fin])
            ->with('lecteur.user', 'exemplaire.livre')
            ->orderBy('date_emprunt', 'desc')
            ->get();

        $stats = [
            'total_emprunts' => $emprunts->count(),
            'en_cours' => $emprunts->where('statut', 'en_cours')->count(),
            'termines' => $emprunts->where('statut', 'termine')->count(),
            'en_retard' => $emprunts->where('statut', 'en_retard')->count(),
            'livres_uniques' => $emprunts->pluck('exemplaire.livre_id')->unique()->count(),
            'lecteurs_actifs' => $emprunts->pluck('lecteur_id')->unique()->count(),
        ];

        $data = [
            'emprunts' => $emprunts,
            'stats' => $stats,
            'mois' => $debut->format('F Y'),
            'periode' => $debut->format('d/m/Y') . ' - ' . $fin->format('d/m/Y'),
            'date_generation' => now()->format('d/m/Y H:i'),
        ];

        $pdf = PDF::loadView('rapports.mensuel-emprunts', $data);
        
        return $pdf->download('rapport-emprunts-' . $debut->format('Y-m') . '.pdf');
    }

    /**
     * Rapport des retards et pénalités (PDF)
     */
    public function rapportRetardsPenalites(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $empruntsEnRetard = Emprunt::where('statut', 'en_retard')
            ->with('lecteur.user', 'exemplaire.livre')
            ->orderBy('date_retour_prevue', 'asc')
            ->get();

        $penalites = Penalite::where('statut', 'impayee')
            ->with('lecteur.user', 'emprunt.exemplaire.livre')
            ->orderBy('montant', 'desc')
            ->get();

        $stats = [
            'total_retards' => $empruntsEnRetard->count(),
            'total_penalites_impayees' => $penalites->count(),
            'montant_total_impaye' => $penalites->sum('montant'),
            'lecteurs_concernes' => $empruntsEnRetard->pluck('lecteur_id')->unique()->count(),
        ];

        $data = [
            'emprunts_retard' => $empruntsEnRetard,
            'penalites' => $penalites,
            'stats' => $stats,
            'date_generation' => now()->format('d/m/Y H:i'),
        ];

        $pdf = PDF::loadView('rapports.retards-penalites', $data);
        
        return $pdf->download('rapport-retards-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Rapport statistiques annuelles (PDF)
     */
    public function rapportAnnuel(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $annee = $request->get('annee', now()->year);
        $debut = Carbon::create($annee, 1, 1)->startOfYear();
        $fin = Carbon::create($annee, 12, 31)->endOfYear();

        // Statistiques générales
        $stats = [
            'total_livres' => Livre::count(),
            'total_emprunts' => Emprunt::whereBetween('date_emprunt', [$debut, $fin])->count(),
            'total_lecteurs' => Lecteur::whereHas('user', function($q) use ($debut) {
                $q->where('created_at', '<=', $debut->endOfYear());
            })->count(),
            'nouveaux_lecteurs' => Lecteur::whereHas('user', function($q) use ($debut, $fin) {
                $q->whereBetween('created_at', [$debut, $fin]);
            })->count(),
            'total_penalites' => Penalite::whereBetween('date_creation', [$debut, $fin])->count(),
            'montant_penalites' => Penalite::whereBetween('date_creation', [$debut, $fin])
                ->where('statut', 'payee')
                ->sum('montant'),
        ];

        // Emprunts par mois
        $empruntsParMois = Emprunt::whereBetween('date_emprunt', [$debut, $fin])
            ->select(
                DB::raw('EXTRACT(MONTH FROM date_emprunt) as mois'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Top 10 livres
        $topLivres = Emprunt::whereBetween('date_emprunt', [$debut, $fin])
            ->select('exemplaire_id', DB::raw('COUNT(*) as nb_emprunts'))
            ->groupBy('exemplaire_id')
            ->orderBy('nb_emprunts', 'desc')
            ->limit(10)
            ->with('exemplaire.livre')
            ->get();

        $data = [
            'annee' => $annee,
            'stats' => $stats,
            'emprunts_par_mois' => $empruntsParMois,
            'top_livres' => $topLivres,
            'date_generation' => now()->format('d/m/Y H:i'),
        ];

        $pdf = PDF::loadView('rapports.annuel', $data);
        
        return $pdf->download('rapport-annuel-' . $annee . '.pdf');
    }

   /**
 * Export Excel - Inventaire des livres
 */
    public function exportInventaire(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // ✅ Utiliser la classe Export
        return Excel::download(
            new InventaireLivresExport, 
            'inventaire-livres-' . now()->format('Y-m-d') . '.xlsx'
        );
    }



    public function exportEmprunts(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $filters = [
            'statut' => $request->get('statut'),
            'date_debut' => $request->get('date_debut'),
            'date_fin' => $request->get('date_fin'),
        ];

        return Excel::download(
            new EmpruntsExport($filters), 
            'emprunts-' . now()->format('Y-m-d') . '.xlsx'
        );
    }


    /**
     * Export CSV - Liste simple
     */
    public function exportEmpruntsCsv(Request $request)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['administrateur', 'bibliothecaire'])) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $emprunts = Emprunt::with('lecteur.user', 'exemplaire.livre')
            ->orderBy('date_emprunt', 'desc')
            ->get();

        $csv = "ID;Lecteur;Livre;Date Emprunt;Date Retour Prévue;Statut\n";
        
        foreach ($emprunts as $emprunt) {
            $csv .= sprintf(
                "%d;%s %s;%s;%s;%s;%s\n",
                $emprunt->id,
                $emprunt->lecteur->user->prenom,
                $emprunt->lecteur->user->nom,
                $emprunt->exemplaire->livre->titre,
                $emprunt->date_emprunt->format('d/m/Y'),
                $emprunt->date_retour_prevue->format('d/m/Y'),
                $emprunt->statut
            );
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="emprunts-' . now()->format('Y-m-d') . '.csv"');
    }
}