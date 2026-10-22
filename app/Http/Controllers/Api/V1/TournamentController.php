<?php
// --- Файл: app/Http/Controllers/Api/V1/TournamentController.php ---

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TournamentCollection;
use App\Http\Resources\V1\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new TournamentCollection(Tournament::where('status', true)->paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Tournament $tournament)
    {
        $tournament->load(['seasons' => function ($query) {
            $query->orderBy('is_current', 'desc')->orderBy('start_date', 'desc');
        }]);
        
        return new TournamentResource($tournament);
    }
}