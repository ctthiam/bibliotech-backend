<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relations
    public function lecteur()
    {
        return $this->hasOne(Lecteur::class);
    }

    public function bibliothecaire()
    {
        return $this->hasOne(Bibliothecaire::class);
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'destinataire_id');
    }

    // Vérifier les rôles
    public function isLecteur()
    {
        return $this->role === 'lecteur';
    }

    public function isBibliothecaire()
    {
        return $this->role === 'bibliothecaire';
    }

    public function isAdministrateur()
    {
        return $this->role === 'administrateur';
    }
}