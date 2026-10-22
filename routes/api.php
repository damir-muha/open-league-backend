<?php
// --- Файл: routes/api.php ---

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\PersonController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\SeasonController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TournamentController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\TransferRequestController; 
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\AdvertisementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SeasonRosterController;
use App\Http\Controllers\Api\V1\VenueController;  
use App\Http\Controllers\Api\Vi\SuspensionController;

// --- Публичные маршруты ---
Route::prefix('v1')->group(function () {
    // Аутентификация
    Route::post('/login', [AuthController::class, 'login']);
     
    // Все GET-запросы, которые мы делали раньше
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show']);
    Route::get('/seasons', [SeasonController::class, 'index']);
    Route::get('/seasons/{season}', [SeasonController::class, 'show']);
    Route::get('/seasons/{season}/teams', [SeasonController::class, 'teams']);
    Route::get('/seasons/{season}/games', [SeasonController::class, 'games']);
    Route::get('/seasons/{season}/standings', [SeasonController::class, 'standings']);
    Route::get('/seasons/{season}/registered-players', [SeasonController::class, 'registeredPlayers']);
    Route::get('/seasons/{season}/stats-leaders', [SeasonController::class, 'statsLeaders']);  
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);
    Route::get('/teams/{team}/staff', [TeamController::class, 'staff']);
    Route::get('/teams/{team}/roster', [TeamController::class, 'roster']);
	Route::get('/teams/{team}/transfers', [TeamController::class, 'transfers']);
    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/{player}', [PlayerController::class, 'show']);
    Route::get('/players/{player}/stats', [PlayerController::class, 'stats']);
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/upcoming-rounds', [GameController::class, 'upcomingRounds']);
    Route::get('/games/{game}', [GameController::class, 'show']);
    Route::get('/games/{game}/lineups', [GameController::class, 'lineups']);
    Route::get('/games/{game}/events', [GameController::class, 'events']);
    Route::get('/people', [PersonController::class, 'index']);
    Route::get('/people/{person}', [PersonController::class, 'show']);
    Route::get('/news', [NewsController::class, 'index']);
    Route::get('/news/{news:slug}', [NewsController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/ads', [AdvertisementController::class, 'index']);
	Route::get('/venues', [VenueController::class, 'index']); 
});
 

// --- Защищенные маршруты ---
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::apiResource('/suspensions', \App\Http\Controllers\Api\V1\SuspensionController::class)->only(['index', 'store', 'destroy']);
	
	// --- ДОБАВЬТЕ ЭТУ СТРОКУ ---
    Route::post('/players', [PlayerController::class, 'store']);
	Route::match(['PUT', 'PATCH'], '/players/{player}', [PlayerController::class, 'update']);
    Route::post('/players/{player}', [PlayerController::class, 'update']);
    // ----------------------------

    // Для менеджера
    Route::post('/teams/{team}/roster', [TeamController::class, 'storeRoster']);
    Route::post('/games/{game}/lineups', [GameController::class, 'storeLineup']);
	Route::delete('/games/{game}/lineups', [GameController::class, 'destroyLineup']);
    Route::get('/games/{game}/available-players', [GameController::class, 'availablePlayersForLineup']);
	
	Route::patch('/rosters/{roster}/players/{player}', [SeasonRosterController::class, 'updatePlayerJerseyNumber']);
    
    // Для судьи
    Route::put('/games/{game}/score', [GameController::class, 'updateScore']);
    Route::post('/games/{game}/events', [GameController::class, 'storeEvent']);
	Route::delete('/games/{game}/events/{event}', [GameController::class, 'destroyEvent']);

    // Для трансферов
    Route::get('/transfers', [TransferRequestController::class, 'index']);
    Route::post('/transfers', [TransferRequestController::class, 'store']);
    Route::put('/transfers/{transferRequest}', [TransferRequestController::class, 'update']);
	
	Route::patch('/games/{game}/details', [GameController::class, 'updateDetails']);
});
