<?php

namespace App\Http\Controllers;

use App\Models\MediaGallery;
use Illuminate\Http\Request;

class MediaGalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaGallery::query();

        if ($request->has('bambino_id')) {
            $query->where('bambino_id', $request->bambino_id);
        }
        
        if ($request->has('classe_id')) {
            // Include foto della classe O foto specifiche del bambino
            $query->where('classe_id', $request->classe_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4',
            'classe_id' => 'nullable|exists:classi,_id',
            'bambino_id' => 'nullable|exists:bambini,_id'
        ]);

        // Esempio base di upload locale (In prod usare S3)
        $path = $request->file('file')->store('gallery', 'public');

        $media = MediaGallery::create([
            'bambino_id' => $request->bambino_id,
            'classe_id' => $request->classe_id,
            'file_path' => $path,
            'tipo_file' => str_contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image',
            'descrizione' => $request->input('descrizione')
        ]);

        return response()->json($media, 201);
    }
}