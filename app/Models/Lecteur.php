<?php

// ============================================
// 2. MODÈLE Lecteur (app/Models/Lecteur.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecteur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numero_carte',
        'date_naissance',
        'statut',
        'quota_emprunt',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function penalites()
    {
        return $this->hasMany(Penalite::class);
    }

    // Méthodes utilitaires
    public function empruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en_cours')->get();
    }

    public function nombreEmpruntsEnCours()
    {
        return $this->emprunts()->where('statut', 'en_cours')->count();
    }

    public function peutEmprunter()
    {
        return $this->statut === 'actif' 
            && $this->nombreEmpruntsEnCours() < $this->quota_emprunt;
    }

    public function penalitesImpayees()
    {
        return $this->penalites()->where('statut', 'impayee')->get();
    }

    public function montantTotalPenalites()
    {
        return $this->penalites()->where('statut', 'impayee')->sum('montant');
    }
}
