<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransferRequest;
use App\Http\Requests\Api\V1\UpdateTransferRequest;
use App\Http\Resources\V1\TransferRequestResource;
use App\Models\SeasonRoster;
use App\Models\TransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransferRequestController extends Controller
{
	use AuthorizesRequests; 

    public function index(Request $request)
    {
        // Admin only: view all transfers
        $query = TransferRequest::with(['player', 'fromTeam', 'toTeam', 'season']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('season_id')) {
            $query->where('season_id', $request->season_id);
        }
        
        return TransferRequestResource::collection($query->latest()->paginate(20));
    }
	
    public function store(StoreTransferRequest $request)
    {
        $this->authorize('create', TransferRequest::class);

        $validated = $request->validated();

        $validated['requested_by_manager_id'] = Auth::id();

        $transfer = TransferRequest::create($validated);

        return new TransferRequestResource($transfer);
    }

    public function update(UpdateTransferRequest $request, TransferRequest $transferRequest)
    {
        $this->authorize('update', $transferRequest);

        $validated = $request->validated();

        if ($validated['status'] === 'approved') {
            
            $fromRoster = SeasonRoster::where('season_id', $transferRequest->season_id)
                ->where('team_id', $transferRequest->from_team_id)->first();
            
            $toRoster = SeasonRoster::firstOrCreate(
                ['season_id' => $transferRequest->season_id, 'team_id' => $transferRequest->to_team_id]
            );

            if ($fromRoster) {
                $fromRoster->players()->detach($transferRequest->player_id);
            }
            $toRoster->players()->attach($transferRequest->player_id, ['season_id' => $transferRequest->season_id]);

            $transferRequest->approved_by_manager_id = Auth::id();
        }

        $transferRequest->status = $validated['status'];
        $transferRequest->save();

        return new TransferRequestResource($transferRequest);
    }
}
