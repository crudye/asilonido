<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Classe extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'classi';

    protected $fillable = [
        'nome',             // Es. "I Pesciolini"
        'anno_scolastico',  // Es. "2024/2025"
        'capacita_max',     // Numero intero
        'educatori_ids'     // Array di ID degli educatori assegnati
    ];

    protected $casts = [
        //'educatori_ids' => 'array',
        'capacita_max' => 'integer'
    ];

    public function bambini()
    {
        return $this->hasMany(Bambino::class, 'classe_id');
    }
}