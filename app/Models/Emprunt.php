<?php

// ============================================
// 8. MODÈLE Emprunt (app/Models/Emprunt.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Emprunt extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecteur_id',
        'exemplaire_id',
        'date_emprunt',
        'date_retour_prevue',
        'date_retour_effective',
        'statut',
        'nombre_prolongations',
    ];

    protected $casts = [
        'date_emprunt' => 'datetime',
        'date_retour_prevue' => 'date',
        'date_retour_effective' => 'date',
    ];

    // Relations
    public function lecteur()
    {
        return $this->belongsTo(Lecteur::class);
    }

    public function exemplaire()
    {
        return $this->belongsTo(Exemplaire::class);
    }

    public function penalites()
    {
        return $this->hasMany(Penalite::class);
    }

    // Méthodes utilitaires
    public function estEnRetard()
    {
        return $this->statut === 'en_cours' 
            && Carbon::now()->isAfter($this->date_retour_prevue);
    }

    public function joursDeRetard()
    {
        if (!$this->estEnRetard()) {
            return 0;
        }
        return Carbon::now()->diffInDays($this->date_retour_prevue);
    }

    public function peutEtreProlonge()
    {
        return $this->statut === 'en_cours' 
            && $this->nombre_prolongations < 2
            && !$this->exemplaire->livre->reservations()->where('statut', 'en_attente')->exists();
    }

    public function prolonger($jours = 7)
    {
        if ($this->peutEtreProlonge()) {
            $this->date_retour_prevue = Carbon::parse($this->date_retour_prevue)->addDays($jours);
            $this->nombre_prolongations++;
            $this->save();
            return true;
        }
        return false;
    }

    public function retourner()
    {
        $this->date_retour_effective = Carbon::now();
        $this->statut = 'termine';
        $this->save();
        
        $this->exemplaire->marquerCommeDisponible();
    }
}
