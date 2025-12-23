<?php

namespace App\Http\Controllers;

use App\Models\RegistroPresenze;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RegistroPresenzeController extends Controller
{
    /**
     * Elenco presenze. 
     * Filtra per data (default oggi) e classe.
     */
    public function index(Request $request)
    {
        $data = $request->input('data', Carbon::today()->format('Y-m-d'));
        
        $query = RegistroPresenze::with('bambino')->where('data', $data);

        if ($request->has('classe_id')) {
            $query->whereHas('bambino', function($q) use ($request) {
                $q->where('classe_id', $request->classe_id);
            });
        }

        return response()->json($query->get());
    }

    /**
     * Check-in (Ingresso) o Segnalazione Assenza
     */
    public function store(Request $request)
    {
        $request->validate([
            'bambino_id' => 'required|exists:bambini,_id',
            'data' => 'required|date',
            'orario_ingresso' => 'nullable|date_format:H:i',
            'assenza_motivo' => 'nullable|string'
        ]);

        // Upsert: se esiste già, aggiorna
        $presenza = RegistroPresenze::updateOrCreate(
            [
                'bambino_id' => $request->bambino_id,
                'data' => $request->data
            ],
            $request->all()
        );

        return response()->json($presenza);
    }

    /**
     * Check-out (Uscita)
     */
    public function update(Request $request, $id)
    {
        $presenza = RegistroPresenze::findOrFail($id);
        
        // Aggiorna solo l'uscita
        $presenza->update([
            'orario_uscita' => $request->input('orario_uscita', Carbon::now()),
            'accompagnatore_uscita' => $request->input('accompagnatore_uscita')
        ]);

        return response()->json($presenza);
    }
}