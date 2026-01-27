<?php

namespace App\Http\Controllers;

use App\Models\Bambino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia; // Importiamo la facciata di Inertia
use App\Models\Classe; // Necessario per la select nel form

class BambinoController extends Controller
{
    
    /**
     * Mostra il form vuoto per creare un nuovo bambino.
     */
    public function create($id = null)
    {
        //dd($id);

        if($id){
            // Siamo in modifica scheda esistente
            $bambino = Bambino::findOrFail($id);
        }

        // Recuperiamo le classi per popolare la select nel form
        // Map per assicurare che l'ID sia stringa
        $classi = Classe::all(['_id', 'nome'])->map(function ($classe) {
            $classe->id = (string) $classe->_id;
            return $classe;
        });

        return Inertia::render('BambinoForm', [
            'classi' => $classi,
            'bambino' => !isset($bambino) ? null : $bambino // Se in modifica, passiamo i dati esistenti
        ]);
    }
    
    /**
     * Lista bambini.
     * Renderizza la pagina Vue con i dati pre-caricati.
     */
    public function index(Request $request)
    {
        $query = Bambino::with('classe');

        // Filtro per Classe
        if ($request->has('classe_id') && $request->classe_id) {
            $query->where('classe_id', $request->classe_id);
        }

        // Filtro per stato (Default: true/attivi se non specificato diversamente)
        if ($request->has('attivo')) {
            $query->where('is_attivo', $request->boolean('attivo'));
        } else {
             // Opzionale: di default mostriamo solo gli attivi per pulizia
             $query->where('is_attivo', true);
        }

        return Inertia::render('BambiniIndex', [
            // I dati principali filtrati
            'bambini' => $query->orderBy('created_at', 'desc')->get(),
            
            // Dati accessori per la UI (es. dropdown nel modale "Nuovo Bambino")
            'classi' => Classe::all(['_id', 'nome']),
            
            // Passiamo indietro i filtri attivi per mantenerli nella UI
            'filters' => $request->only(['classe_id', 'attivo']),
        ]);
    }    

    public function store(Request $request)
    {
        
        //dd($request->all());
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cognome' => 'required|string|max:255',
            'classe_id' => 'required',
            'data_nascita' => 'required|date',
            //'codice_fiscale' => 'required|string|unique:bambinos,codice_fiscale',
            'codice_fiscale' => 'required|string',
            'sesso' => 'required|in:M,F',
            'allergie' => 'nullable|array',
            'deleghe_ritiro' => 'nullable|array',
        ]);

        $validated['is_attivo'] = true;
        // Assicuriamoci che allergie sia un array anche se null
        $validated['allergie'] = $request->input('allergie', []); 
        
        /*$idsBambini = Bambino::all(['_id'])->map(function ($bambino) {
            return (string) $bambino->_id;
        })->toArray();

        if(in_array($request->input('id'), $idsBambini)){            
            $bambino = Bambino::findOrFail($request->input('id'));
            $bambino->update($validated);

            return redirect()->back()->with('success', 'Scheda aggiornata correttamente');
        }else{
            Bambino::create($validated);
            return redirect()->back()->with('success', 'Bambino iscritto con successo');
        }*/

        Bambino::updateOrCreate(
            ['_id' => $request->input('id')],
            $validated
        );
    }

    /**
     * Dettaglio singolo bambino.
     * Nota: Se questa view non esiste ancora, potresti volerla creare o usare un modale nella index.
     * Qui ipotizzo tu voglia una pagina dedicata al profilo.
     */
    public function show($id)
    {
        $bambino = Bambino::with(['classe', 'diariDiBordo' => function($q) {
            $q->limit(10)->orderBy('data', 'desc');
        }])->findOrFail($id);

        return Inertia::render('BambinoShow', [
            'bambino' => $bambino
        ]);
    }

    /**
     * Aggiornamento dati.
     */
    public function update(Request $request, $id)
    {
        $bambino = Bambino::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'string|max:255',
            'cognome' => 'string|max:255',
            'classe_id' => 'exists:classi,_id',
            'data_nascita' => 'date',
            'allergie' => 'nullable|array',
            'deleghe_ritiro' => 'nullable|array',
            'is_attivo' => 'boolean'
        ]);

        $bambino->update($validated);

        return redirect()->back()->with('success', 'Scheda aggiornata correttamente');
    }

    /**
     * Eliminazione (Soft Delete logica).
     */
    public function destroy($id)
    {
        $bambino = Bambino::findOrFail($id);
        
        $bambino->update(['is_attivo' => false]);

        return redirect()->back()->with('success', 'Bambino archiviato correttamente');
    }
}