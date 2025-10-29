<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emprunt;
use App\Models\Exemplaire;
use App\Models\Lecteur;
use App\Models\Penalite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmpruntController extends Controller
{
    /**
     * LISTE DES EMPRUNTS
     * GET /api/emprunts
     * - Lecteur : voit ses propres emprunts
     * - Bibliothécaire/Admin : voit tous les emprunts
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();
        
        $query = Emprunt::with(['lecteur.user', 'exemplaire.livre.categorie']);

        // Si c'est un lecteur, il ne voit que ses emprunts
        if ($user->isLecteur()) {
            $query->where('lecteur_id', $user->lecteur->id);
        }

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('lecteur_id') && !$user->isLecteur()) {
            $query->where('lecteur_id', $request->lecteur_id);
        }

        // Tri
        $query->orderBy('date_emprunt', 'desc');

        $emprunts = $query->paginate($request->get('per_page', 15));

        // Formater les données
        $emprunts->getCollection()->transform(function ($emprunt) {
            return [
                'id' => $emprunt->id,
                'lecteur' => [
                    'id' => $emprunt->lecteur->id,
                    'nom' => $emprunt->lecteur->user->nom,
                    'prenom' => $emprunt->lecteur->user->prenom,
                    'numero_carte' => $emprunt->lecteur->numero_carte,
                ],
                'livre' => [
                    'id' => $emprunt->exemplaire->livre->id,
                    'titre' => $emprunt->exemplaire->livre->titre,
                    'auteur' => $emprunt->exemplaire->livre->auteur,
                    'image_couverture' => $emprunt->exemplaire->livre->image_couverture,
                ],
                'exemplaire' => [
                    'id' => $emprunt->exemplaire->id,
                    'numero_exemplaire' => $emprunt->exemplaire->numero_exemplaire,
                ],
                'date_emprunt' => $emprunt->date_emprunt,
                'date_retour_prevue' => $emprunt->date_retour_prevue,
                'date_retour_effective' => $emprunt->date_retour_effective,
                'statut' => $emprunt->statut,
                'nombre_prolongations' => $emprunt->nombre_prolongations,
                'est_en_retard' => $emprunt->estEnRetard(),
                'jours_de_retard' => $emprunt->joursDeRetard(),
                'peut_etre_prolonge' => $emprunt->peutEtreProlonge(),
                'created_at' => $emprunt->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $emprunts
        ], 200);
    }

    /**
     * EMPRUNTER UN LIVRE
     * POST /api/emprunts
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();

        // Validation
        $validator = Validator::make($request->all(), [
            'exemplaire_id' => 'required|exists:exemplaires,id',
        ], [
            'exemplaire_id.required' => 'L\'exemplaire est obligatoire',
            'exemplaire_id.exists' => 'Exemplaire non trouvé',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que l'utilisateur est un lecteur
        if (!$user->isLecteur()) {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les lecteurs peuvent emprunter des livres'
            ], 403);
        }

        $lecteur = $user->lecteur;

        // Vérifier le statut du lecteur
        if ($lecteur->statut !== 'actif') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est ' . $lecteur->statut . '. Veuillez contacter la bibliothèque.'
            ], 403);
        }

        // Vérifier les pénalités impayées
        if ($lecteur->montantTotalPenalites() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez des pénalités impayées. Veuillez les régler avant d\'emprunter.',
                'penalites_impayees' => $lecteur->montantTotalPenalites()
            ], 403);
        }

        // Vérifier le quota d'emprunts
        if (!$lecteur->peutEmprunter()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez atteint votre quota d\'emprunts (' . $lecteur->quota_emprunt . ')',
                'emprunts_en_cours' => $lecteur->nombreEmpruntsEnCours()
            ], 400);
        }

        // Vérifier que l'exemplaire existe et est disponible
        $exemplaire = Exemplaire::with('livre')->find($request->exemplaire_id);

        if (!$exemplaire->estDisponible()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet exemplaire n\'est pas disponible (statut: ' . $exemplaire->statut . ')'
            ], 400);
        }

        // Créer l'emprunt (durée par défaut : 14 jours)
        $dureeEmprunt = 14;
        $emprunt = Emprunt::create([
            'lecteur_id' => $lecteur->id,
            'exemplaire_id' => $exemplaire->id,
            'date_emprunt' => now(),
            'date_retour_prevue' => Carbon::now()->addDays($dureeEmprunt),
            'statut' => 'en_cours',
        ]);

        // Marquer l'exemplaire comme emprunté
        $exemplaire->marquerCommeEmprunte();

        // Charger les relations pour la réponse
        $emprunt->load(['lecteur.user', 'exemplaire.livre']);

        return response()->json([
            'success' => true,
            'message' => 'Livre emprunté avec succès',
            'data' => [
                'id' => $emprunt->id,
                'livre' => [
                    'titre' => $emprunt->exemplaire->livre->titre,
                    'auteur' => $emprunt->exemplaire->livre->auteur,
                ],
                'date_emprunt' => $emprunt->date_emprunt,
                'date_retour_prevue' => $emprunt->date_retour_prevue,
                'statut' => $emprunt->statut,
            ]
        ], 201);
    }

    /**
     * PROLONGER UN EMPRUNT
     * POST /api/emprunts/{id}/prolonger
     */
    public function prolonger($id)
    {
        $user = auth('api')->user();
        $emprunt = Emprunt::with(['lecteur', 'exemplaire.livre'])->find($id);

        if (!$emprunt) {
            return response()->json([
                'success' => false,
                'message' => 'Emprunt non trouvé'
            ], 404);
        }

        // Vérifier que c'est bien l'emprunt du lecteur connecté
        if ($user->isLecteur() && $emprunt->lecteur->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet emprunt ne vous appartient pas'
            ], 403);
        }

        // Vérifier si la prolongation est possible
        if (!$emprunt->peutEtreProlonge()) {
            $raison = 'Impossible de prolonger : ';
            if ($emprunt->statut !== 'en_cours') {
                $raison .= 'l\'emprunt n\'est plus en cours';
            } elseif ($emprunt->nombre_prolongations >= 2) {
                $raison .= 'limite de prolongations atteinte (2 maximum)';
            } else {
                $raison .= 'le livre est réservé par un autre lecteur';
            }

            return response()->json([
                'success' => false,
                'message' => $raison
            ], 400);
        }

        // Prolonger de 7 jours
        $emprunt->prolonger(7);

        return response()->json([
            'success' => true,
            'message' => 'Emprunt prolongé de 7 jours',
            'data' => [
                'id' => $emprunt->id,
                'nouvelle_date_retour' => $emprunt->date_retour_prevue,
                'nombre_prolongations' => $emprunt->nombre_prolongations,
            ]
        ], 200);
    }

    /**
     * RETOURNER UN LIVRE
     * POST /api/emprunts/{id}/retourner
     */
    public function retourner($id)
    {
        $user = auth('api')->user();
        $emprunt = Emprunt::with(['lecteur.user', 'exemplaire'])->find($id);

        if (!$emprunt) {
            return response()->json([
                'success' => false,
                'message' => 'Emprunt non trouvé'
            ], 404);
        }

        // Vérifier les permissions
        if ($user->isLecteur() && $emprunt->lecteur->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet emprunt ne vous appartient pas'
            ], 403);
        }

        // Vérifier que l'emprunt est bien en cours
        if ($emprunt->statut !== 'en_cours') {
            return response()->json([
                'success' => false,
                'message' => 'Cet emprunt a déjà été retourné'
            ], 400);
        }

        // Vérifier si retard et créer pénalité
        $penalite = null;
        if ($emprunt->estEnRetard()) {
            $joursRetard = $emprunt->joursDeRetard();
            $montant = Penalite::calculerMontant($joursRetard);

            $penalite = Penalite::create([
                'lecteur_id' => $emprunt->lecteur_id,
                'emprunt_id' => $emprunt->id,
                'montant' => $montant,
                'motif' => "Retard de {$joursRetard} jour(s)",
                'statut' => 'impayee',
            ]);
        }

        // Marquer le retour
        $emprunt->retourner();

        $response = [
            'success' => true,
            'message' => 'Livre retourné avec succès',
            'data' => [
                'emprunt_id' => $emprunt->id,
                'date_retour_effective' => $emprunt->date_retour_effective,
                'statut' => $emprunt->statut,
            ]
        ];

        if ($penalite) {
            $response['message'] = 'Livre retourné avec retard';
            $response['data']['penalite'] = [
                'montant' => $penalite->montant,
                'jours_retard' => $joursRetard,
                'motif' => $penalite->motif,
            ];
        }

        return response()->json($response, 200);
    }

    /**
     * MES EMPRUNTS EN COURS (pour le lecteur connecté)
     * GET /api/mes-emprunts
     */
    public function mesEmprunts()
    {
        $user = auth('api')->user();

        if (!$user->isLecteur()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette route est réservée aux lecteurs'
            ], 403);
        }

        $emprunts = Emprunt::with(['exemplaire.livre.categorie'])
            ->where('lecteur_id', $user->lecteur->id)
            ->where('statut', 'en_cours')
            ->orderBy('date_retour_prevue', 'asc')
            ->get();

        $data = $emprunts->map(function($emprunt) {
            return [
                'id' => $emprunt->id,
                'livre' => [
                    'id' => $emprunt->exemplaire->livre->id,
                    'titre' => $emprunt->exemplaire->livre->titre,
                    'auteur' => $emprunt->exemplaire->livre->auteur,
                    'image_couverture' => $emprunt->exemplaire->livre->image_couverture,
                ],
                'date_emprunt' => $emprunt->date_emprunt->format('d/m/Y'),
                'date_retour_prevue' => $emprunt->date_retour_prevue->format('d/m/Y'),
                'jours_restants' => Carbon::now()->diffInDays($emprunt->date_retour_prevue, false),
                'est_en_retard' => $emprunt->estEnRetard(),
                'jours_de_retard' => $emprunt->joursDeRetard(),
                'peut_etre_prolonge' => $emprunt->peutEtreProlonge(),
                'nombre_prolongations' => $emprunt->nombre_prolongations,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'emprunts' => $data,
                'total' => $emprunts->count(),
                'quota' => $user->lecteur->quota_emprunt,
            ]
        ], 200);
    }

    /**
     * HISTORIQUE DES EMPRUNTS (pour le lecteur connecté)
     * GET /api/historique-emprunts
     */
    public function historique()
    {
        $user = auth('api')->user();

        if (!$user->isLecteur()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette route est réservée aux lecteurs'
            ], 403);
        }

        $emprunts = Emprunt::with(['exemplaire.livre'])
            ->where('lecteur_id', $user->lecteur->id)
            ->where('statut', 'termine')
            ->orderBy('date_retour_effective', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $emprunts
        ], 200);
    }

    /**
     * STATISTIQUES DES EMPRUNTS (Bibliothécaire/Admin)
     * GET /api/emprunts/statistiques
     */
    public function statistiques()
    {
        $user = auth('api')->user();

        if (!$user->isBibliothecaire() && !$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $stats = [
            'emprunts_en_cours' => Emprunt::where('statut', 'en_cours')->count(),
            'emprunts_en_retard' => Emprunt::where('statut', 'en_cours')
                ->where('date_retour_prevue', '<', now())
                ->count(),
            'emprunts_ce_mois' => Emprunt::whereMonth('date_emprunt', now()->month)
                ->whereYear('date_emprunt', now()->year)
                ->count(),
            'total_emprunts' => Emprunt::count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ], 200);
    }

        /**
     * EMPRUNTER UN LIVRE PAR SON ID (nouvelle route)
     * POST /api/emprunts/par-livre
     */
    public function emprunterParLivre(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'livre_id' => 'required|exists:livres,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Trouver un exemplaire disponible pour ce livre
        $exemplaire = Exemplaire::where('livre_id', $request->livre_id)
            ->where('statut', 'disponible')
            ->first();

        if (!$exemplaire) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun exemplaire disponible pour ce livre'
            ], 400);
        }

        // Utiliser la méthode store existante
        $request->merge(['exemplaire_id' => $exemplaire->id]);
        return $this->store($request);
    }
}