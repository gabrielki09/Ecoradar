<?php

namespace App\Http\Controllers\CollectionRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\RewardPointsRequest;
use App\Http\Resources\RewardEntryResource;
use App\Services\Collection\RewardPointService;

class RewardPointController extends Controller
{
    public function show(RewardPointsRequest $request, RewardPointService $rewards)
    {
        return apiSuccess('Saldo de pontos de recompensa.', [
            'balance' => $rewards->balance($request->user(), $request->validated('company_id')),
        ]);
    }

    public function history(RewardPointsRequest $request, RewardPointService $rewards)
    {
        $history = $rewards->history($request->user(), $request->validated('company_id'), $request->integer('per_page', 15));

        return apiSuccess('Histórico de pontos de recompensa.', [
            'history' => [
                'data' => RewardEntryResource::collection($history->items()),
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ]);
    }
}
