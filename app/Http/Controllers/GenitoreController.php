<?php

namespace App\Http\Controllers;

use App\Models\Genitore;
use Illuminate\Http\Request;

class GenitoreController extends Controller
{
    public function index()
    {
        return response()->json(Genitore::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'telefono' => 'required|string',
            'indirizzo' => 'string',
            'bambini_ids' => 'array'
        ]);

        $genitore = Genitore::create($validated);
        return response()->json($genitore, 201);
    }

    public function show($id)
    {
        // Mostra il genitore e carica i dati dei figli
        $genitore = Genitore::findOrFail($id);
        // Aggiungiamo i dati completi dei bambini alla risposta
        $genitore->dati_figli = $genitore->bambini; 
        
        return response()->json($genitore);
    }

    public function update(Request $request, $id)
    {
        $genitore = Genitore::findOrFail($id);
        $genitore->update($request->all());
        return response()->json($genitore);
    }
}