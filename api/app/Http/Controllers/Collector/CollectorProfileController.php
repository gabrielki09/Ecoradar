<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collector\CollectorProfileUpdateRequest;
use App\Services\Collector\CollectorProfileService;
use Illuminate\Http\Request;

class CollectorProfileController extends Controller
{
    public function __construct(
        protected CollectorProfileService $collectorProfileService
    ){}

    public function show(Request $req)
    {
        return apiSuccess(
            'Dados do perfil',
            [
                'collector_profile' => $this->collectorProfileService->show($req->user()->id)
            ]
        );
    }

    public function update(CollectorProfileUpdateRequest $req)
    {
        return apiSuccess(
            'Perfil de coleta cadastrado com sucesso!',
            [
                'collector_profile' => $this->collectorProfileService->update(
                    collectorProfileId: $req->user()->collectorProfile->id,
                    data: $req->validated()
                )
            ]
        );
    }
}
