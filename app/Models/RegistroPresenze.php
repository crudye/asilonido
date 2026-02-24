<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class RegistroPresenze extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'registro_presenze';

    protected $fillable = [
        'bambino_id', 
        'data_selezionata',                  // YYYY-MM-DD
        'orario_ingresso',       // Timestamp o stringa ora
        'orario_uscita',
        'accompagnatore_ingresso', // Nome di chi lo porta
        'accompagnatore_uscita',   // Nome di chi lo prende
        'assenza_motivo'         // "Malattia", "Viaggio" etc.
    ];

    protected $casts = [
        'orario_ingresso' => 'datetime',
        'orario_uscita' => 'datetime'
    ];

    public function bambino()
    {
        return $this->belongsTo(Bambino::class, 'bambino_id');
    }
}