<?php

namespace App\Http\Controllers\CollectionRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\AcceptRequest;
use App\Http\Requests\Collection\AvailableCollectionRequestsRequest;
use App\Http\Requests\Collection\CancelRequest;
use App\Http\Requests\Collection\CollectedRequest;
use App\Http\Requests\Collection\CollectionRequestStoreRequest;
use App\Http\Requests\Collection\CompleteRequest;
use App\Http\Requests\Filters\FilterV1Request;
use App\Services\Collection\CollectionRequestService;

class CollectionRequestController extends Controller
{
    public function __construct(
        protected CollectionRequestService $collectionRequestService
    ){}

    public function store(CollectionRequestStoreRequest $req)
    {
        return apiSuccess(
            'Coleta cadastrada com sucesso!',
            [
                'collection_request' => $this->collectionRequestService->store(
                    user: $req->user(),
                    data: $req->validated()
                )
            ]
        );
    }

    public function mine(FilterV1Request $req)
    {
        return apiSuccess(
            'Suas coletas',
            [
                'collection_request' => $this->collectionRequestService->all(
                    user: $req->user(),
                    parameters: $req->validated()
                )
            ]
        );
    }

    public function available(AvailableCollectionRequestsRequest $req)
    {
        $data = $req->validated();

        return apiSuccess(
            'Pedidos disponíveis.',
            [
                'collection_requests' => $this->collectionRequestService->available(
                    user: $req->user(),
                    companyId: $data['company_id'] ?? null,
                    parameters: $data
                )
            ]
        );
    }

    public function details(int $id)
    {
        return apiSuccess(
            'Dados da coleta.',
            [
                'collection_request' => $this->collectionRequestService->details($id)
            ]
        );
    }

    public function accept(AcceptRequest $req)
    {
        return apiSuccess(
            'Coleta aceitada com sucesso!',
            [
                'collection_request' => $this->collectionRequestService->accept(
                    user: $req->user(),
                    data: $req->validated()
                )
            ]
        );
    }

    public function collected(CollectedRequest $req)
    {
        return apiSuccess(
            'Resíduos coletados com sucesso!',
            [
                'collection_request' => $this->collectionRequestService->collected(
                    user: $req->user(),
                    data: $req->validated()
                )
            ]
        );
    }

    public function complete(CompleteRequest $req)
    {
        return apiSuccess(
            'Coleta concluída com sucesso!',
            [
                'collection_request' => $this->collectionRequestService->complete(
                    user: $req->user(),
                    data: $req->validated()
                )
            ]
        );
    }

    public function cancel(CancelRequest $req)
    {
        return apiSuccess(
            'Coleta cancelada com sucesso!',
            [
                'collection_request' => $this->collectionRequestService->cancel(
                    user: $req->user(),
                    data: $req->validated()
                )
            ]
        );
    }
}
