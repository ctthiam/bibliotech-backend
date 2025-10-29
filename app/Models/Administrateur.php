<?php

// ============================================
// 4. MODÈLE Administrateur (app/Models/Administrateur.php)
// ============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'privileges',
    ];

    protected $casts = [
        'privileges' => 'array',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
