<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collector\CollectorProfileUpdateRequest;
use App\Services\Collector\CollectorProfileService;
use App\Enum\ProfileTypes;
use App\Support\CollectionAccess;
use Illuminate\Http\Request;

class CollectorProfileController extends Controller
{
    public function __construct(
        protected CollectorProfileService $collectorProfileService
    ){}

    public function show(Request $req)
    {
        abort_unless($req->user()->profile_type === ProfileTypes::COLLECTOR && CollectionAccess::provider($req->user()), 403);
        $profile = $this->collectorProfileService->show($req->user()->id);
        abort_unless($profile, 404);

        return apiSuccess(
            'Dados do perfil',
            [
                'collector_profile' => $profile
            ]
        );
    }

    public function update(CollectorProfileUpdateRequest $req)
    {
        $profile = $req->user()->collectorProfile;
        abort_unless($profile, 404);

        return apiSuccess(
            'Perfil de coleta cadastrado com sucesso!',
            [
                'collector_profile' => $this->collectorProfileService->update(
                    collectorProfileId: $profile->id,
                    data: $req->validated()
                )
            ]
        );
    }
}
