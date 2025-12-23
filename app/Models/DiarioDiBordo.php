<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class DiarioDiBordo extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'diari_di_bordo';

    protected $fillable = [
        'bambino_id', 'data', 'pasti', 'sonno', 'bisogni', 'attivita', 'umore'
    ];

    protected $casts = [
        'data' => 'datetime', // Usa datetime per compatibilità con Carbon
        // RIMOSSI i cast a 'array' per evitare l'errore json_decode
    ];

    public function bambino()
    {
        return $this->belongsTo(Bambino::class, 'bambino_id');
    }
}