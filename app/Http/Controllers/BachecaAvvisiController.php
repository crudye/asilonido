<?php

namespace App\Http\Controllers;

use App\Models\BachecaAvvisi;
use Illuminate\Http\Request;

class BachecaAvvisiController extends Controller
{
    public function index(Request $request)
    {
        // Logica semplificata: ritorna tutti. 
        // In produzione filtreresti per la classe del figlio dell'utente loggato.
        return response()->json(BachecaAvvisi::orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titolo' => 'required|string',
            'contenuto' => 'required|string',
            'tipo' => 'required|in:avviso,evento,urgente',
            'destinatari_classi' => 'nullable|array',
            'richiede_conferma' => 'boolean'
        ]);

        $post = BachecaAvvisi::create($validated);
        return response()->json($post, 201);
    }

    /**
     * Segna come letto da un genitore
     */
    public function segnaComeLetto(Request $request, $id)
    {
        $post = BachecaAvvisi::findOrFail($id);
        $userId = $request->user_id; // O Auth::id();

        // Push atomico in array MongoDB se non esiste già
        $post->push('letto_da_user_ids', $userId, true); 

        return response()->json(['message' => 'Conferma lettura registrata']);
    }
}