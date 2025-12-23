<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Bambino extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bambini';

    protected $fillable = [
        'classe_id', 'nome', 'cognome', 'data_nascita', 
        'codice_fiscale', 'sesso', 'allergie', 'deleghe_ritiro', 'is_attivo'
    ];

    /**
     * NOTA: Con MongoDB NON devi mettere il cast a 'array' o 'json' 
     * per i campi che sono già array nel database.
     * Lasciamo solo i cast per le date.
     */
    protected $casts = [
        'data_nascita' => 'datetime', // 'datetime' gestisce meglio il formato BSON Date di Mongo
        'is_attivo' => 'boolean'
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }
    
    public function diariDiBordo()
    {
        return $this->hasMany(DiarioDiBordo::class, 'bambino_id');
    }
}