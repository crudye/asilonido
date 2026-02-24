<?php

namespace App\Http\Controllers;

use App\Models\RegistroPresenze;
use App\Models\Bambino;
use App\Models\Classe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class RegistroPresenzeController extends Controller
{
    /**
     * Elenco presenze per la data selezionata.
     */
    public function index(Request $request)
    {
        //dd($request->all());    
    // 1. Data selezionata (default oggi)
        $data = $request->input('dataSelezionata', Carbon::today()->format('Y-m-d'));

        // 2. Query Bambini (Attivi)
        $queryBambini = Bambino::with('classe')->where('is_attivo', true);

        if ($request->has('classe_id') && $request->classe_id) {
            $queryBambini->where('classe_id', $request->classe_id);
        }

        // Convertiamo gli ObjectId in stringhe per il frontend
        $bambini = $queryBambini->orderBy('cognome')->get()->map(function ($b) {
            $b->id = (string) $b->_id;
            return $b;
        });

        // 3. Recupera le presenze già registrate per questa data
        $presenze = RegistroPresenze::where('data_selezionata', $data)
            ->whereIn('bambino_id', $bambini->pluck('id'))
            ->get()
            ->map(function ($p) {
                $p->bambino_id = (string) $p->bambino_id;
                // Formattiamo gli orari per l'input HTML time (H:i)
                $p->ora_in_fmt = $p->orario_ingresso ? Carbon::parse($p->orario_ingresso)->format('H:i') : null;
                $p->ora_out_fmt = $p->orario_uscita ? Carbon::parse($p->orario_uscita)->format('H:i') : null;
                return $p;
            })
            ->keyBy('bambino_id');
        //dd($bambini);
        return Inertia::render('PresenzeIndex', [
            'bambini' => $bambini,
            'presenzeEsistenti' => $presenze, // Oggetto { "ID_BAMBINO": { ...dati presenza... } }
            'dataSelezionata' => $data,
            'classi' => Classe::all(['_id', 'nome'])->map(function($c){ $c->id = (string)$c->_id; return $c; }),
            'filters' => $request->only(['classe_id', 'data'])
        ]);
    }

    /**
     * Check-in (Ingresso) / Check-out / Assenza
     * Gestisce tutto tramite Upsert
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            
            'bambino_id' => 'required|string', 
            'data_selezionata' => 'required|date',
            'orario_ingresso' => 'nullable', // Accetta stringa H:i
            'orario_uscita' => 'nullable',   // Accetta stringa H:i
            'accompagnatore_ingresso' => 'nullable|string',
            'accompagnatore_uscita' => 'nullable|string',
            'assenza_motivo' => 'nullable|string'
        ]);

        // Se arriva una stringa orario (es "09:30"), la combiniamo con la data per salvare un DateTime completo
        if (!empty($validated['orario_ingresso'])) {
            $validated['orario_ingresso'] = Carbon::parse($validated['data_selezionata'] . ' ' . $validated['orario_ingresso']);
        }
        
        if (!empty($validated['orario_uscita'])) {
            $validated['orario_uscita'] = Carbon::parse($validated['data_selezionata'] . ' ' . $validated['orario_uscita']);
        }

        RegistroPresenze::updateOrCreate(
            [
                'bambino_id' => $validated['bambino_id'],
                'data_selezionata' => $validated['data_selezionata']
            ],
            $validated
        );

        return redirect()->back()->with('success', 'Registro aggiornato');
    }
}