<?php

namespace App\Http\Controllers\CollectionRequest;

use App\Support\CollectionAccess;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\AcceptRequest;
use App\Http\Requests\Collection\AvailableCollectionRequestsRequest;
use App\Http\Requests\Collection\CancelRequest;
use App\Http\Requests\Collection\CollectedRequest;
use App\Http\Requests\Collection\CollectionRequestStoreRequest;
use App\Http\Requests\Collection\CompleteRequest;
use App\Http\Requests\Filters\FilterV1Request;
use App\Services\Collection\CollectionRequestService;
use Illuminate\Http\Request;

class CollectionRequestController extends Controller
{
    public function __construct(
        protected CollectionRequestService $collectionRequestService
    ){}

    private function authorizeRequester(Request $request): void
    {
        abort_unless(CollectionAccess::requester($request->user()), 403);
    }

    private function authorizeProvider(Request $request): void
    {
        abort_unless(CollectionAccess::provider($request->user()), 403);
    }

    public function all(FilterV1Request $req)
    {
        $this->authorizeProvider($req);
        return apiSuccess(
            'Todas as coletas do disponíveis.',
            [
                'collection_requests' => $this->collectionRequestService->all(
                    parameters: $req->validated()
                )
            ]
        );
    }

    public function allForProvider(AvailableCollectionRequestsRequest $req)
    {
        $this->authorizeProvider($req);
        return apiSuccess(
            'Todas as coletas do prestador.',
            [
                'collection_requests' => $this->collectionRequestService->allForProvider(
                    user: $req->user(),
                    parameters: $req->validated()
                )
            ]
        );
    }

    public function mine(FilterV1Request $req)
    {
        $this->authorizeRequester($req);
        return apiSuccess(
            'Todas as suas coletas.',
            [
                'collection_request' => $this->collectionRequestService->mine(
                    user: $req->user(),
                    parameters: $req->validated()
                )
            ]
        );
    }

    public function store(CollectionRequestStoreRequest $req)
    {
        $this->authorizeRequester($req);
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

    public function available(AvailableCollectionRequestsRequest $req)
    {
        $this->authorizeProvider($req);
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

    public function details(Request $req, int $id)
    {
        $user = $req->user();
        $isRequester = CollectionAccess::requester($user);
        if (!$isRequester) $this->authorizeProvider($req);
        $collectionRequest = $this->collectionRequestService->details($id);
        abort_unless($collectionRequest, 404);
        if ($isRequester) abort_unless($collectionRequest->requester_user_id === $user->id, 403);

        return apiSuccess(
            'Dados da coleta.',
            [
                'collection_request' => $collectionRequest
            ]
        );
    }

    public function accept(AcceptRequest $req)
    {
        $this->authorizeProvider($req);
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
        $this->authorizeProvider($req);
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
        $this->authorizeProvider($req);
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
        $this->authorizeRequester($req);
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
