<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MenuMensa extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'menu_mensa';

    protected $fillable = [
        'data',         // YYYY-MM-DD
        'piatti',       // Oggetto JSON {primo:..., secondo:...}
        'allergeni'     // Array
    ];

    protected $casts = [
        'data' => 'date',
        'piatti' => 'array',
        'allergeni' => 'array'
    ];
}