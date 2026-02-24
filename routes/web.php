<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\BambinoController;
use App\Http\Controllers\DiarioDiBordoController;
use App\Http\Controllers\RegistroPresenzeController;
use App\Http\Controllers\MenuMensaController;
use App\Http\Controllers\BachecaAvvisiController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Redirect root to bambini
Route::redirect('/', '/bambini');

// --- SEZIONE BAMBINI ---
// Chiama BambinoController@index che passa { bambini, classi, filters }
Route::get('/bambini/crea', [BambinoController::class, 'create'])->name('bambini.create');
Route::get('/bambini', [BambinoController::class, 'index'])->name('bambini.index');
Route::post('/bambini', [BambinoController::class, 'store'])->name('bambini.store');
Route::get('/bambini/{id}', [BambinoController::class, 'create'])->name('bambini.update'); // Mostra il form di modifica -> chiama questo metodo updateOrCreate
Route::delete('/bambini/{id}', [BambinoController::class, 'destroy'])->name('bambini.destroy');

// --- SEZIONE DIARIO ---
// Chiama DiarioDiBordoController@index che passa { bambini, diariEsistenti, dataSelezionata }
Route::get('/diario', [DiarioDiBordoController::class, 'index'])->name('diario.index');
Route::post('/diario', [DiarioDiBordoController::class, 'store'])->name('diario.store');

// --- SEZIONE PRESENZE ---
// Deve passare { presenze, bambini }
Route::get('/presenze', [RegistroPresenzeController::class, 'index'])->name('presenze.index');
Route::post('/presenze', [RegistroPresenzeController::class, 'store'])->name('presenze.store');

// --- SEZIONE MENSA ---
// Deve passare { menuSettimana }
Route::get('/mensa', [MenuMensaController::class, 'index'])->name('mensa.index');
Route::post('/mensa', [MenuMensaController::class, 'store'])->name('mensa.store');
Route::delete('/mensa/{id}', [MenuMensaController::class, 'destroy'])->name('mensa.destroy');

// --- SEZIONE AVVISI ---
// Deve passare { avvisi }
Route::get('/avvisi', [BachecaAvvisiController::class, 'index'])->name('avvisi.index');
Route::post('/avvisi', [BachecaAvvisiController::class, 'store'])->name('avvisi.store');


require __DIR__.'/settings.php';
