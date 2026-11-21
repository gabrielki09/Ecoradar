<?php

namespace App\Http\Controllers\CollectionPoint;

use App\Http\Controllers\Controller;
use App\Http\Requests\CollectionPoint\CollectionPointRequest;
use App\Http\Requests\CollectionPoint\Store\CollectionPointStoreRequest;
use App\Http\Requests\CollectionPoint\Update\CollectionPointUpdateRequest;
use App\Http\Requests\Filters\FilterV1Request;
use App\Services\Collection\CollectionPointService;
use Illuminate\Http\Request;

class CollectionPointController extends Controller
{
    public function __construct(
        protected CollectionPointService $collectionPointService
    ){}
    /**
     * Display a listing of the resource.
     */
    public function all(FilterV1Request $req)
    {
        return apiSuccess(
            'Todos os pontos de coleta.',
            [
                'collection_points' => $this->collectionPointService->all($req->validated())
            ]
        );
    }

    public function allForProvider(CollectionPointRequest $req)
    {
        return apiSuccess(
            'Todos os seus pontos de coleta.',
            [
                'collection_points' => $this->collectionPointService->allForProvider(
                    user: $req->user(),
                    parameters: $req->validated()
                )
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CollectionPointStoreRequest $req)
    {
        return apiSuccess(
            'Ponto de coleta cadastrado com sucesso!',
            [
                'collection_point' => $this->collectionPointService->store(
                    user: $req->user(),
                    data: $req->validated()
                )
            ],
            201
        );
    }

    public function show(CollectionPointRequest $req, int $id)
    {
        return apiSuccess(
            'Dados do ponto de coleta.',
            [
                'collection_point' => $this->collectionPointService->show(
                    user: $req->user(),
                    parameters: $req->validated(),
                    id: $id
                )
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CollectionPointUpdateRequest $req, int $id)
    {
        return apiSuccess(
            'Dados do ponto de coleta alterado com sucesso!',
            [
                'collection_point' => $this->collectionPointService->update(
                    user: $req->user(),
                    data: $req->validated(),
                    id: $id
                )
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CollectionPointRequest $req, int $id)
    {
        return apiSuccess(
            'Sucesso',
            data: $this->collectionPointService->destroy(
                user: $req->user(),
                parameters: $req->validated(),
                id: $id
            ),
            status: 202
        );
    }

    public function recovery(CollectionPointRequest $req, int $id)
    {
        return apiSuccess(
            'Registro restaurado!',
            data: $this->collectionPointService->recovery(
                user: $req->user(),
                parameters: $req->validated(),
                id: $id
            )
        );
    }
}
