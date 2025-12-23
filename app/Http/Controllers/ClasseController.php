<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function index()
    {
        // Ritorna le classi con il conteggio dei bambini iscritti
        return response()->json(Classe::withCount('bambini')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string',
            'anno_scolastico' => 'required|string',
            'capacita_max' => 'required|integer',
            'educatori_ids' => 'nullable|array'
        ]);

        $classe = Classe::create($validated);
        return response()->json($classe, 201);
    }

    public function show($id)
    {
        return response()->json(Classe::with('bambini')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $classe = Classe::findOrFail($id);
        $classe->update($request->all());
        return response()->json($classe);
    }

    public function destroy($id)
    {
        Classe::findOrFail($id)->delete();
        return response()->json(['message' => 'Classe eliminata']);
    }
}