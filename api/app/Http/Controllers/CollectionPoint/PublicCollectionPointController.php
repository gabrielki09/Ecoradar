<?php

namespace App\Http\Controllers\CollectionPoint;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\FilterV1Request;
use App\Services\Collection\CollectionPointService;

class PublicCollectionPointController extends Controller
{
    public function __construct(
        protected CollectionPointService $collectionPointService
    ){}
    
    public function index(FilterV1Request $req)
    {
        return apiSuccess(
            'Pontos de coleta públicos.', 
            [
                'collection_points' => $this->collectionPointService->allPublic($req->validated())
            ]
        );
    }
}