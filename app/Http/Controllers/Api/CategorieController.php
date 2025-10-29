<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategorieController extends Controller
{
    /**
     * LISTE DES CATÉGORIES
     * GET /api/categories
     */
    public function index()
    {
        $categories = Categorie::withCount('livres')
            ->orderBy('nom')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    /**
     * DÉTAILS D'UNE CATÉGORIE avec ses livres
     * GET /api/categories/{id}
     */
    public function show($id)
    {
        $categorie = Categorie::with(['livres' => function($query) {
            $query->with('exemplaires');
        }])->find($id);

        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $categorie
        ], 200);
    }

    /**
     * CRÉER UNE CATÉGORIE (Admin/Bibliothécaire)
     * POST /api/categories
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user->isBibliothecaire() && !$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:categories,nom',
            'description' => 'nullable|string',
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'nom.unique' => 'Cette catégorie existe déjà',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $categorie = Categorie::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $categorie
        ], 201);
    }

    /**
     * MODIFIER UNE CATÉGORIE
     * PUT /api/categories/{id}
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user->isBibliothecaire() && !$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255|unique:categories,nom,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $categorie->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie modifiée avec succès',
            'data' => $categorie
        ], 200);
    }

    /**
     * SUPPRIMER UNE CATÉGORIE
     * DELETE /api/categories/{id}
     */
    public function destroy($id)
    {
        $user = auth('api')->user();
        if (!$user->isAdministrateur()) {
            return response()->json([
                'success' => false,
                'message' => 'Seul un administrateur peut supprimer une catégorie'
            ], 403);
        }

        $categorie = Categorie::find($id);

        if (!$categorie) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie non trouvée'
            ], 404);
        }

        // Vérifier qu'il n'y a pas de livres
        if ($categorie->livres()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer : cette catégorie contient des livres'
            ], 400);
        }

        $categorie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès'
        ], 200);
    }
}