<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livre;
use App\Models\Exemplaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LivreController extends Controller
{
    /**
     * LISTE DES LIVRES avec recherche et filtres
     * GET /api/livres
     */
    public function index(Request $request)
    {
        $query = Livre::with(['categorie', 'exemplaires']);

        // Recherche par titre, auteur ou ISBN
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titre', 'ILIKE', "%{$search}%")
                  ->orWhere('auteur', 'ILIKE', "%{$search}%")
                  ->orWhere('isbn', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par catégorie
        if ($request->has('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        // Filtre par disponibilité
        if ($request->has('disponible') && $request->disponible == 'true') {
            $query->whereHas('exemplaires', function($q) {
                $q->where('statut', 'disponible');
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $livres = $query->paginate($perPage);

        // Ajouter des infos calculées
        $livres->getCollection()->transform(function ($livre) {
            return [
                'id' => $livre->id,
                'titre' => $livre->titre,
                'auteur' => $livre->auteur,
                'isbn' => $livre->isbn,
                'editeur' => $livre->editeur,
                'annee_publication' => $livre->annee_publication,
                'langue' => $livre->langue,
                'resume' => $livre->resume,
                'image_couverture' => $livre->image_couverture,
                'categorie' => $livre->categorie ? [
                    'id' => $livre->categorie->id,
                    'nom' => $livre->categorie->nom,
                ] : null,
                'nombre_exemplaires' => $livre->nombreTotalExemplaires(),
                'exemplaires_disponibles' => $livre->nombreExemplairesDisponibles(),
                'est_disponible' => $livre->estDisponible(),
                'created_at' => $livre->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $livres
        ], 200);
    }

    /**
     * DÉTAILS D'UN LIVRE
     * GET /api/livres/{id}
     */
    public function show($id)
    {
        $livre = Livre::with(['categorie', 'exemplaires'])->find($id);

        if (!$livre) {
            return response()->json([
                'success' => false,
                'message' => 'Livre non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $livre->id,
                'titre' => $livre->titre,
                'auteur' => $livre->auteur,
                'isbn' => $livre->isbn,
                'editeur' => $livre->editeur,
                'annee_publication' => $livre->annee_publication,
                'nombre_pages' => $livre->nombre_pages,
                'langue' => $livre->langue,
                'resume' => $livre->resume,
                'image_couverture' => $livre->image_couverture,
                'categorie' => $livre->categorie,
                'exemplaires' => $livre->exemplaires->map(function($ex) {
                    return [
                        'id' => $ex->id,
                        'numero_exemplaire' => $ex->numero_exemplaire,
                        'statut' => $ex->statut,
                        'localisation' => $ex->localisation,
                    ];
                }),
                'nombre_total_exemplaires' => $livre->nombreTotalExemplaires(),
                'exemplaires_disponibles' => $livre->nombreExemplairesDisponibles(),
                'est_disponible' => $livre->estDisponible(),
                'created_at' => $livre->created_at,
            ]
        ], 200);
    }

    /**
     * AJOUTER UN LIVRE (Bibliothécaire/Admin uniquement)
     * POST /api/livres
     */
    public function store(Request $request)
    {
        // Vérifier que l'utilisateur est bibliothécaire ou admin
        $user = auth('api')->user();
        if (!$user->isBibliothecaire() && !$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'auteur' => 'required|string|max:255',
            'isbn' => 'required|string|unique:livres,isbn',
            'editeur' => 'nullable|string|max:255',
            'annee_publication' => 'nullable|integer|min:1000|max:' . date('Y'),
            'nombre_pages' => 'nullable|integer|min:1',
            'langue' => 'nullable|string|max:50',
            'categorie_id' => 'nullable|exists:categories,id',
            'resume' => 'nullable|string',
            'image_couverture' => 'nullable|url',
            'nombre_exemplaires' => 'required|integer|min:1',
        ], [
            'titre.required' => 'Le titre est obligatoire',
            'auteur.required' => 'L\'auteur est obligatoire',
            'isbn.required' => 'L\'ISBN est obligatoire',
            'isbn.unique' => 'Ce livre existe déjà',
            'nombre_exemplaires.required' => 'Le nombre d\'exemplaires est obligatoire',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Créer le livre
        $livre = Livre::create([
            'titre' => $request->titre,
            'auteur' => $request->auteur,
            'isbn' => $request->isbn,
            'editeur' => $request->editeur,
            'annee_publication' => $request->annee_publication,
            'nombre_pages' => $request->nombre_pages,
            'langue' => $request->langue ?? 'français',
            'categorie_id' => $request->categorie_id,
            'resume' => $request->resume,
            'image_couverture' => $request->image_couverture,
        ]);

        // Créer les exemplaires automatiquement
        $nombreExemplaires = $request->nombre_exemplaires;
        for ($i = 1; $i <= $nombreExemplaires; $i++) {
            Exemplaire::create([
                'livre_id' => $livre->id,
                'numero_exemplaire' => $livre->isbn . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'statut' => 'disponible',
                'localisation' => $request->localisation ?? 'Non défini',
                'date_acquisition' => now(),
            ]);
        }

        $livre->load(['categorie', 'exemplaires']);

        return response()->json([
            'success' => true,
            'message' => 'Livre ajouté avec succès',
            'data' => $livre
        ], 201);
    }

    /**
     * MODIFIER UN LIVRE (Bibliothécaire/Admin uniquement)
     * PUT /api/livres/{id}
     */
    public function update(Request $request, $id)
    {
        // Vérifier que l'utilisateur est bibliothécaire ou admin
        $user = auth('api')->user();
        if (!$user->isBibliothecaire() && !$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $livre = Livre::find($id);

        if (!$livre) {
            return response()->json([
                'success' => false,
                'message' => 'Livre non trouvé'
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'titre' => 'sometimes|string|max:255',
            'auteur' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:livres,isbn,' . $id,
            'editeur' => 'nullable|string|max:255',
            'annee_publication' => 'nullable|integer|min:1000|max:' . date('Y'),
            'nombre_pages' => 'nullable|integer|min:1',
            'langue' => 'nullable|string|max:50',
            'categorie_id' => 'nullable|exists:categories,id',
            'resume' => 'nullable|string',
            'image_couverture' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Mettre à jour
        $livre->update($request->only([
            'titre', 'auteur', 'isbn', 'editeur', 'annee_publication',
            'nombre_pages', 'langue', 'categorie_id', 'resume', 'image_couverture'
        ]));

        $livre->load(['categorie', 'exemplaires']);

        return response()->json([
            'success' => true,
            'message' => 'Livre modifié avec succès',
            'data' => $livre
        ], 200);
    }

    /**
     * SUPPRIMER UN LIVRE (Admin uniquement)
     * DELETE /api/livres/{id}
     */
    public function destroy($id)
    {
        // Vérifier que l'utilisateur est admin
        $user = auth('api')->user();
        if (!$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Seul un administrateur peut supprimer un livre'
            ], 403);
        }

        $livre = Livre::find($id);

        if (!$livre) {
            return response()->json([
                'success' => false,
                'message' => 'Livre non trouvé'
            ], 404);
        }

        // Vérifier qu'aucun exemplaire n'est emprunté
        $exemplairesEmpruntes = $livre->exemplaires()->where('statut', 'emprunte')->count();
        if ($exemplairesEmpruntes > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : des exemplaires sont actuellement empruntés'
            ], 400);
        }

        $livre->delete(); // Les exemplaires seront supprimés automatiquement (cascade)

        return response()->json([
            'success' => true,
            'message' => 'Livre supprimé avec succès'
        ], 200);
    }

    /**
     * LIVRES POPULAIRES
     * GET /api/livres/populaires
     */
    public function populaires()
    {
        $livres = Livre::with(['categorie'])
            ->withCount(['exemplaires as emprunts_count' => function($query) {
                $query->whereHas('emprunts');
            }])
            ->orderBy('emprunts_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $livres
        ], 200);
    }

    /**
     * NOUVEAUX LIVRES
     * GET /api/livres/nouveaux
     */
    public function nouveaux()
    {
        $livres = Livre::with(['categorie'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $livres
        ], 200);
    }
}