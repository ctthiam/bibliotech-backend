<?php
// ============================================
// app/Models/Reservation.php
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecteur_id',
        'livre_id',
        'date_reservation',
        'statut',
    ];

    protected $casts = [
        'date_reservation' => 'datetime',
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
    public function estEnAttente()
    {
        return $this->statut === 'en_attente';
    }

    public function estDisponible()
    {
        return $this->statut === 'disponible';
    }

    public function estExpiree()
    {
        return $this->statut === 'expiree';
    }

    public function marquerDisponible()
    {
        $this->statut = 'disponible';
        $this->save();
    }

    public function annuler()
    {
        $this->statut = 'annulee';
        $this->save();
    }

    // Automatically set date_reservation on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (!$reservation->date_reservation) {
                $reservation->date_reservation = now();
            }
        });
    }
}