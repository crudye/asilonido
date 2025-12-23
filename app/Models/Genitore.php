<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Genitore extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'genitori';

    protected $fillable = [
        'user_id',       // ID dell'utente autenticato
        'telefono',
        'indirizzo',
        'bambini_ids'    // Array di ID dei figli
    ];

    protected $casts = [
        'bambini_ids' => 'array'
    ];

    // Helper per recuperare i modelli dei figli
    public function getBambiniAttribute()
    {
        return Bambino::whereIn('_id', $this->bambini_ids ?? [])->get();
    }
}