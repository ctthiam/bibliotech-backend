<?php
// ============================================
// app/Models/Notification.php
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
        'donnees',
        'lu',
        'date_lecture',
        'date_envoi',
    ];

    protected $casts = [
        'donnees' => 'array',
        'lu' => 'boolean',
        'date_lecture' => 'datetime',
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
        $this->lu = true;
        $this->date_lecture = now();
        $this->save();
    }

    public function estLue()
    {
        return $this->lu;
    }

    // Automatically set date_envoi on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (!$notification->date_envoi) {
                $notification->date_envoi = now();
            }
        });
    }

    // Scopes
    public function scopeNonLues($query)
    {
        return $query->where('lu', false);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecentes($query, $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }
}