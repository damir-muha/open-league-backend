<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProtocolController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\TournamentController;
use App\Http\Controllers\Web\TeamController;
use App\Http\Controllers\Web\PlayerController;
use App\Http\Controllers\Web\GameController;
use App\Http\Controllers\Web\NewsController;

//Route::get('/', function () {return view('welcome');});

Route::get('/admin/games/{game}/protocol/{type}', [ProtocolController::class, 'print'])
    ->middleware(['auth', 'verified']) // Защищаем роут
    ->name('admin.protocol.print');

Route::redirect('/', '/admin'); 

//Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [NewsController::class, 'show'])->name('news.show');
