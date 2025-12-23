<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BachecaAvvisi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bacheca_avvisi';

    protected $fillable = [
        'titolo',
        'contenuto',
        'tipo',                 // 'avviso', 'evento', 'urgente'
        'destinatari_classi',   // Array di ID classi (null = tutti)
        'richiede_conferma',    // Boolean
        'letto_da_user_ids'     // Array ID genitori che hanno letto
    ];

    protected $casts = [
        'destinatari_classi' => 'array',
        'letto_da_user_ids' => 'array',
        'richiede_conferma' => 'boolean'
    ];
}