<?php

namespace App\Http\Controllers;

use App\Models\MenuMensa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MenuMensaController extends Controller
{
    public function index(Request $request)
    {
        // Ritorna il menu della settimana corrente se non specificato
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        return response()->json(
            MenuMensa::whereBetween('data', [$start, $end])->orderBy('data')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|date',
            'piatti' => 'required|array',
            'allergeni' => 'nullable|array'
        ]);

        $menu = MenuMensa::updateOrCreate(
            ['data' => $validated['data']], // Se esiste data, aggiorna
            $validated
        );

        return response()->json($menu);
    }
}