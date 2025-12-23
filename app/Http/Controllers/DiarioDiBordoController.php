<?php

namespace App\Http\Controllers;

use App\Models\DiarioDiBordo;
use App\Models\Bambino;
use App\Models\Classe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DiarioDiBordoController extends Controller
{
    /**
     * Visualizza la dashboard del diario.
     * Carica i bambini e i diari esistenti per la data selezionata.
     */
    public function index(Request $request)
    {
        // 1. Determina la data (Oggi di default se non passata)
        $dataString = $request->input('giorno', Carbon::today()->format('Y-m-d'));

        $inizioGiorno = Carbon::createFromFormat('Y-m-d', $dataString)->startOfDay();
        $fineGiorno   = Carbon::createFromFormat('Y-m-d', $dataString)->endOfDay();
        
        // 2. Query Bambini (per il selettore in alto)
        // Se c'è un filtro classe, lo applichiamo
        $queryBambini = Bambino::with('classe')->where('is_attivo', true);
        
        if ($request->has('classe_id') && $request->classe_id) {
            $queryBambini->where('classe_id', $request->classe_id);
        }

        $bambini = $queryBambini->orderBy('cognome')->get();
        
        $idsBambini = $bambini->pluck('_id')->map(function ($id) {
            return (string) $id;
        })->toArray();
        
        // 3. Recupera i diari GIA' ESISTENTI per questa data
        // Li indicizziamo per 'bambino_id' così il frontend può trovarli subito
        // quando clicchi sulla faccia del bambino.
        $diariDelGiorno = DiarioDiBordo::whereBetween('data', [$inizioGiorno, $fineGiorno])
            ->whereIn('bambino_id', $idsBambini)
            ->get()
            ->keyBy('bambino_id');               
        //dd($diariDelGiorno);
        return Inertia::render('DiarioGiorno', [
            'bambini' => $bambini,
            'diariEsistenti' => $diariDelGiorno, // Oggetto { "ID_BAMBINO": { ...dati diario... } }
            'dataSelezionata' => $dataString,
            'classi' => Classe::all(['_id', 'nome']), // Utile se vuoi aggiungere un filtro classe in alto
            'filters' => $request->only(['classe_id', 'data'])
        ]);
    }

    /**
     * Crea o Aggiorna il diario (Upsert).
     */
    public function store(Request $request)
    {
        //dd($request->all());
        
        $validated = $request->validate([
            'bambino_id' => 'required',
            'giorno' => 'required|date|date_format:Y-m-d',
            'pasti' => 'nullable|array',
            'sonno' => 'nullable|array',
            'bisogni' => 'nullable|array',
            'attivita' => 'nullable|string',
            'umore' => 'nullable|string',
        ],
        [
            'bambino_id.required' => 'Seleziona un bambino valido.',
            'giorno.required' => 'La data è obbligatoria.',
            'giorno.date' => 'La data deve essere una data valida.',
            'giorno.date_format' => 'La data deve essere nel formato YYYY-MM-DD.',
        ]);
        
        // Logica Upsert: Cerca per bambino+data, se trova aggiorna, altrimenti crea.
        // UpdateOrCreate è molto comodo qui.
        DiarioDiBordo::updateOrCreate(
            [
                'bambino_id' => $validated['bambino_id'],
                'data' => $validated['giorno']
            ],
            [
                'pasti' => $request->input('pasti'),
                'sonno' => $request->input('sonno'),
                'bisogni' => $request->input('bisogni'),
                'attivita' => $request->input('attivita'),
                'umore' => $request->input('umore'),
            ]
        );

        // Redirect back con messaggio flash
        // preserveScroll: true è gestito lato frontend nella chiamata form.post, 
        // ma il back() è essenziale lato server.
        return redirect()->back()->with('success', 'Diario aggiornato con successo');
        
    }

    /**
     * Visualizzazione singolo diario (se necessaria una pagina di sola lettura).
     */
    public function show($id)
    {
        $diario = DiarioDiBordo::with('bambino.classe')->findOrFail($id);
        
        return Inertia::render('DiarioShow', [
            'diario' => $diario
        ]);
    }

    /**
     * Aggiornamento puntuale.
     * Utile se hai una view separata per l'edit, ma spesso nel diario giornaliero 
     * si usa la store con logica upsert per tutto.
     */
    public function update(Request $request, $id)
    {
        $diario = DiarioDiBordo::findOrFail($id);
        $diario->update($request->all());

        return redirect()->back()->with('success', 'Modifica salvata');
    }

    /**
     * Eliminazione diario.
     */
    public function destroy($id)
    {
        $diario = DiarioDiBordo::findOrFail($id);
        $diario->delete();

        return redirect()->back()->with('success', 'Diario rimosso');
    }
}