<?php

// ============================================
// 11. MODÈLE Notification (app/Models/Notification.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'destinataire_id',
        'type',
        'titre',
        'contenu',
        'date_envoi',
        'statut',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
    ];

    // Relations
    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    // Méthodes utilitaires
    public function marquerCommeLue()
    {
        $this->statut = 'lue';
        $this->save();
    }

    public function estLue()
    {
        return $this->statut === 'lue';
    }
}