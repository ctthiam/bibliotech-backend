<?php

// ============================================
// 6. MODÈLE Livre (app/Models/Livre.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'auteur',
        'isbn',
        'editeur',
        'annee_publication',
        'nombre_pages',
        'langue',
        'categorie_id',
        'resume',
        'image_couverture',
    ];

    // Relations
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function exemplaires()
    {
        return $this->hasMany(Exemplaire::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Méthodes utilitaires
    public function exemplairesDisponibles()
    {
        return $this->exemplaires()->where('statut', 'disponible')->get();
    }

    public function nombreExemplairesDisponibles()
    {
        return $this->exemplaires()->where('statut', 'disponible')->count();
    }

    public function estDisponible()
    {
        return $this->nombreExemplairesDisponibles() > 0;
    }

    public function nombreTotalExemplaires()
    {
        return $this->exemplaires()->count();
    }
}
