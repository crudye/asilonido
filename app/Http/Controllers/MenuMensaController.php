<?php

namespace App\Http\Controllers;

use App\Models\MenuMensa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class MenuMensaController extends Controller
{
    /**
     * Mostra il menu della settimana.
     */
    public function index(Request $request)
    {
        // Se non viene passata una data, partiamo da oggi
        $riferimento = $request->input('inizio_settimana', Carbon::today()->format('Y-m-d'));
        
        $inizioSettimana = Carbon::parse($riferimento)->startOfWeek(); // Lunedì
        $fineSettimana = Carbon::parse($riferimento)->endOfWeek();     // Domenica

        // Recuperiamo i menu della settimana dal DB
        $menuDb = MenuMensa::whereBetween('data', [
            $inizioSettimana->format('Y-m-d'), 
            $fineSettimana->format('Y-m-d')
        ])->get();

        // Mappiamo i dati per il frontend: 
        // 1. Convertiamo ObjectId in stringa
        // 2. Creiamo la chiave 'data_selezionata' per evitare l'uso della parola riservata 'data' in Vue
        $menuSettimana = $menuDb->map(function ($menu) {
            $menu->id = (string) $menu->_id;
            // Formattiamo la data del DB e la assegniamo alla variabile sicura
            $menu->data_selezionata = Carbon::parse($menu->data)->format('Y-m-d');
            return $menu;
        })->keyBy('data_selezionata'); // Indicizziamo per data per trovarli facilmente in Vue

        return Inertia::render('MensaMenu', [
            'menuSettimana' => $menuSettimana,
            'inizioSettimana' => $inizioSettimana->format('Y-m-d')
        ]);
    }

    /**
     * Crea o Aggiorna il menu per una data specifica (Upsert).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_selezionata' => 'required|date',
            'piatti' => 'required|array',
            'piatti.primo' => 'nullable|string',
            'piatti.secondo' => 'nullable|string',
            'piatti.contorno' => 'nullable|string',
            'piatti.merenda' => 'nullable|string',
            'allergeni' => 'nullable|array' // Riceve un array di stringhe
        ]);

        // Mappiamo la variabile sicura del frontend sulla colonna reale del database ('data')
        //$dataDb = $validated['data_selezionata'];
        //unset($validated['data_selezionata']);
        //$validated['data'] = $dataDb;

        // Se l'array allergeni è null, forziamo array vuoto
        if (!isset($validated['allergeni'])) {
            $validated['allergeni'] = [];
        }

        MenuMensa::updateOrCreate(
            ['data_selezionata' => $validated['data_selezionata']], // Cerca per data
            $validated           // Aggiorna/Crea con i piatti e allergeni
        );

        return redirect()->back()->with('success', 'Menu aggiornato correttamente');
    }

    /**
     * Elimina il menu di un giorno.
     */
    public function destroy($id)
    {
        $menu = MenuMensa::findOrFail($id);
        $menu->delete();

        return redirect()->back()->with('success', 'Menu del giorno eliminato');
    }
}