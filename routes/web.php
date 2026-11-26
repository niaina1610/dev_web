<?php

use App\Http\Controllers\BibliothequeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BibliothequeController::class, 'index'])->name('bibliotheque.index');

Route::post('/livres', [BibliothequeController::class, 'storeLivre'])->name('livres.store');
Route::put('/livres/{isbn}', [BibliothequeController::class, 'updateLivre'])->name('livres.update');
Route::delete('/livres/{isbn}', [BibliothequeController::class, 'destroyLivre'])->name('livres.destroy');

Route::post('/personnes', [BibliothequeController::class, 'storePersonne'])->name('personnes.store');
Route::put('/personnes/{idPersonne}', [BibliothequeController::class, 'updatePersonne'])->name('personnes.update');
Route::delete('/personnes/{idPersonne}', [BibliothequeController::class, 'destroyPersonne'])->name('personnes.destroy');

Route::post('/abonnes', [BibliothequeController::class, 'storeAbonne'])->name('abonnes.store');
Route::put('/abonnes/{idAbonne}', [BibliothequeController::class, 'updateAbonne'])->name('abonnes.update');
Route::delete('/abonnes/{idAbonne}', [BibliothequeController::class, 'destroyAbonne'])->name('abonnes.destroy');
