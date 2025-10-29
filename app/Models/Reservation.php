<?php

// ============================================
// 9. MODÈLE Reservation (app/Models/Reservation.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecteur_id',
        'livre_id',
        'date_reservation',
        'date_expiration',
        'statut',
    ];

    protected $casts = [
        'date_reservation' => 'datetime',
        'date_expiration' => 'date',
    ];

    // Relations
    public function lecteur()
    {
        return $this->belongsTo(Lecteur::class);
    }

    public function livre()
    {
        return $this->belongsTo(Livre::class);
    }

    // Méthodes utilitaires
    public function estActive()
    {
        return in_array($this->statut, ['en_attente', 'disponible']);
    }

    public function marquerCommeDisponible()
    {
        $this->statut = 'disponible';
        $this->date_expiration = Carbon::now()->addDays(3);
        $this->save();
    }

    public function annuler()
    {
        $this->statut = 'annulee';
        $this->save();
    }
}
