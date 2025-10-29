<?php

// ============================================
// 10. MODÈLE Penalite (app/Models/Penalite.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalite extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecteur_id',
        'emprunt_id',
        'montant',
        'motif',
        'date_creation',
        'date_paiement',
        'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_creation' => 'datetime',
        'date_paiement' => 'datetime',
    ];

    // Relations
    public function lecteur()
    {
        return $this->belongsTo(Lecteur::class);
    }

    public function emprunt()
    {
        return $this->belongsTo(Emprunt::class);
    }

    // Méthodes utilitaires
    public function estPayee()
    {
        return $this->statut === 'payee';
    }

    public function marquerCommePayee()
    {
        $this->statut = 'payee';
        $this->date_paiement = now();
        $this->save();
    }

    public static function calculerMontant($joursRetard)
    {
        // 100 FCFA par jour de retard (configurable)
        return $joursRetard * 100;
    }
}
