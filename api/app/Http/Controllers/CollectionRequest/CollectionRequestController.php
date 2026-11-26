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
use App\Enum\ProfileTypes;
use App\Models\Collection\CollectionRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    private const PRIVATE_REQUEST_FIELDS = ['address', 'cep', 'requester', 'requester_user_id'];

    private function limitPreviewFilters(array $parameters): array
    {
        foreach ($parameters['filtersV1'] ?? [] as $index => $filter) {
            if (in_array($filter['field'], self::PRIVATE_REQUEST_FIELDS, true)) {
                throw ValidationException::withMessages([
                    "filtersV1.{$index}.field" => 'O campo não está disponível para esta listagem.',
                ]);
            }
        }

        if (in_array('requester', $parameters['relationships'] ?? [], true)) {
            throw ValidationException::withMessages(['relationships' => 'O solicitante não está disponível para esta listagem.']);
        }

        return $parameters;
    }

    private function preview(CollectionRequest $request): CollectionRequest
    {
        return $request->makeHidden(self::PRIVATE_REQUEST_FIELDS);
    }

    public function all(FilterV1Request $req)
    {
        $this->authorizeProvider($req);
        return apiSuccess(
            'Todas as solicitações de coleta.',
            [
                'collection_requests' => $this->collectionRequestService->all(
                    parameters: $this->limitPreviewFilters($req->validated())
                )->through($this->preview(...))
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
                    parameters: $this->limitPreviewFilters($data)
                )->through($this->preview(...))
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

        $actions = [];
        if (!$isRequester) {
            $companyId = $req->validate(['company_id' => ['sometimes', 'integer', 'min:1']])['company_id'] ?? null;
            if ($user->profile_type !== ProfileTypes::COMPANY || $companyId !== null) {
                $actions = $this->collectionRequestService->providerActions($collectionRequest, resolveProvider($user, $companyId));
            }

            if (!($actions['assigned_to_provider'] ?? false)) {
                $this->preview($collectionRequest);
            }
        }

        return apiSuccess(
            'Dados da coleta.',
            [
                'collection_request' => [...$collectionRequest->toArray(), ...$actions]
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
        $result = $this->collectionRequestService->complete($req->user(), $req->validated());
        $beneficiary = $req->user()->profile_type === ProfileTypes::COMPANY ? 'A empresa recebeu' : 'Você recebeu';

        return apiSuccess(
            "Coleta concluída! {$beneficiary} {$result['points_awarded']} pontos de recompensa.",
            $result
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
