<?php

// ============================================
// 5. MODÈLE Categorie (app/Models/Categorie.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
    ];

    // Relations
    public function livres()
    {
        return $this->hasMany(Livre::class);
    }

    // Méthodes utilitaires
    public function nombreLivres()
    {
        return $this->livres()->count();
    }
}
