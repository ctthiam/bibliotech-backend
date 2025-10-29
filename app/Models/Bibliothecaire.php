<?php

// ============================================
// 3. MODÈLE Bibliothecaire (app/Models/Bibliothecaire.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bibliothecaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service',
        'autorisations',
    ];

    protected $casts = [
        'autorisations' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
