<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MediaGallery extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'media_gallery';

    protected $fillable = [
        'bambino_id',   // Null se è foto di gruppo
        'classe_id',    // Obbligatorio se foto di gruppo
        'file_path',    // URL o path S3
        'tipo_file',    // 'image', 'video'
        'descrizione'
    ];
}