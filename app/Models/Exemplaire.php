<?php

// ============================================
// 7. MODÈLE Exemplaire (app/Models/Exemplaire.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exemplaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'livre_id',
        'numero_exemplaire',
        'statut',
        'localisation',
        'date_acquisition',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
    ];

    // Relations
    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    // Méthodes utilitaires
    public function estDisponible()
    {
        return $this->statut === 'disponible';
    }

    public function estEmprunte()
    {
        return $this->statut === 'emprunte';
    }

    public function empruntActuel()
    {
        return $this->emprunts()->where('statut', 'en_cours')->first();
    }

    public function marquerCommeEmprunte()
    {
        $this->update(['statut' => 'emprunte']);
    }

    public function marquerCommeDisponible()
    {
        $this->update(['statut' => 'disponible']);
    }
}
