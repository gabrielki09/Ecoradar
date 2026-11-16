<?php

namespace App\Services\Collection;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Enum\ProfileTypes;
use App\Exceptions\Collection\CollectionCanceledExcpetion;
use App\Models\Collection\CollectionRequest;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Models\User;
use App\Repositories\Interface\Collection\CollectionHistoryInterface;
use App\Repositories\Interface\Collection\CollectionRequestInterface;
use App\Repositories\Interface\Collection\CollectionRequestItemInterface;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CollectionRequestService
{
    public function __construct(
        protected CollectionRequestInterface $collectionRequestRepository,
        protected CollectionRequestItemInterface $collectionRequestItemRepository,
        protected CollectionHistoryInterface $collectionHistoryRepository
    ){}

    private function storeInHistory(
        CollectionRequest $request,
        CollectionRequestStatus $oldStatus,
        CollectionRequestStatus $newStatus,
        CollectorProfile|Company $provider,
        ?CarbonInterface $acceptanceDate,
        ?CarbonInterface $withdrawalDate,
        ?CarbonInterface $completionDate,
        ?CarbonInterface $canceledDate,
    ): void {
        $this->collectionHistoryRepository->create([
            'collection_request_id' => $request->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'accepted_provider_type' => $provider instanceof CollectorProfile
                ? 'collector_profiles'
                : 'companies',
            'accepted_provider_id' => $provider->id,
            'acceptance_date' => $acceptanceDate ?? null,
            'withdrawal_date' => $withdrawalDate ?? null,
            'completion_date' => $completionDate ?? null,
            'canceled_date' => $canceledDate ?? null,
        ]);
    }

    public function all(User $user, array $parameters)
    {
        return $this->collectionRequestRepository->allForRequester(
            requesterId: $user->id,
            parameters: $parameters
        );
    }

    public function store(User $user, array $data)
    {
        return DB::transaction(function() use ($data, $user) {
            $collectionRequest = $this->collectionRequestRepository->create([
                'requester_user_id' => $user->id,
                'city_id' => $data['city_id'],
                'cep' => $data['cep'],
                'neighborhood' => $data['neighborhood'],
                'address' => $data['address'],
            ]);

            foreach ($data['items'] as $item) {
                $this->collectionRequestItemRepository->create([
                    'collection_request_id' => $collectionRequest->id,
                    'waste_category_id' => $item['waste_category_id'],
                    'description' => $item['description'] ?? null,
                    'approximate_quantity' => $item['approximate_quantity']
                ]);
            }

            return $collectionRequest->load('items');
        });
    }

    private function resolveCollectionProvider(User $user, ?int $companyId): CollectorProfile|Company
    {
        return match ($user->profile_type) {
            ProfileTypes::COLLECTOR =>  $user->collectorProfile
                ?? throw new AuthorizationException('Perfil do coletor não localizado.')
            ,

            ProfileTypes::COMPANY => $user->companies()
                ->whereKey($companyId)
                ->firstOrFail(),

            default => throw new AuthorizationException('Usuário não pode atender coletas.'),
        };
    }

    public function available(
        ?int $companyId,
        User $user,
        array $parameters = [],
    ) {
        $provider = $this->resolveCollectionProvider($user, $companyId);

        return $this->collectionRequestRepository->availableFor(
            cityIds: $provider->cities()
                    ->pluck('cities.id')
                    ->all(),

            categoryIds: $provider->wasteCategories()
                    ->pluck('waste_categories.id')
                    ->all(),

            parameters: $parameters
        );
    }

    public function details(int $id)
    {
        return $this->collectionRequestRepository->findBy(
            parameters: [
                'id' => $id,
            ],

            relationships: [
                'items',
                'requester'
            ]
        );
    }

    private function buildCollectionUpdateData(
        CollectorProfile|Company $provider,
        CollectionRequestStatus $status
    ): array {
        return [
            'status' => $status,

            'accepted_collector_profile_id' => $provider instanceof CollectorProfile
                ? $provider->id
                : null,

            'accepted_company_id' => $provider instanceof Company
                ? $provider->id
                : null,
        ];
    }

    private function validateProviderCanAccept(
        CollectionRequest $request,
        CollectorProfile|Company $provider
    ): void {
        $attendsCity = $provider->cities()
            ->whereKey($request->city_id)
            ->exists();

        if (!$attendsCity) throw new ConflictHttpException('Este prestador não atende a cidade da solicitação');

        $requestCategoryIds = $request->items()
            ->pluck('waste_category_id')
            ->unique()
            ->all();

        if ($requestCategoryIds === []) throw new ConflictHttpException('A solicitação não possui itens para coleta.');

        $providerCategoryIds = $provider->wasteCategories()
            ->pluck('waste_categories.id')
            ->all();

        $unsupportedCategoryIds = array_diff(
            $requestCategoryIds,
            $providerCategoryIds
        );

        if ($unsupportedCategoryIds !== []) throw new ConflictHttpException('Este prestador não atende todas as categorias da solicitação.');
    }

    public function accept(User $user, array $data)
    {
        return DB::transaction(function() use ($user, $data) {
            $provider = $this->resolveCollectionProvider(
                $user,
                $data['company_id'] ?? null
            );

            /**
             * @var \App\Models\Collection\CollectionRequest $request
             */
            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new CollectionCanceledExcpetion('Este pedido já está cancelado');

            if ($request->status !== CollectionRequestStatus::OPEN) throw new ConflictHttpException('Este pedido não está mais disponível.');

            $this->validateProviderCanAccept(
                request: $request,
                provider: $provider
            );

            $oldStatus = $request->status;
            $acceptanceDate = now();

            $dataForUpdate = $this->buildCollectionUpdateData(
                provider: $provider,
                status: CollectionRequestStatus::ACCEPTED
            );

            $dataForUpdate['acceptance_date'] = $acceptanceDate;

            $this->collectionRequestRepository->update(
                $request,
                $dataForUpdate
            );

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::ACCEPTED,
                provider: $provider,
                acceptanceDate: $acceptanceDate,
                withdrawalDate: null,
                completionDate: null,
                canceledDate: null
            );

            return $request->refresh();
        });
    }

    public function collected(
        User $user, array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $provider = $this->resolveCollectionProvider(
                $user,
                $data['company_id'] ?? null
            );

            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            $isAssignedProvider = $provider instanceof CollectorProfile
                ? $request->accepted_collector_profile_id === $provider->id
                : $request->accepted_company_id  === $provider->id;

            if (!$isAssignedProvider) throw new AuthorizationException('Este pedido foi aceito por outro prestador.');

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new CollectionCanceledExcpetion('Este pedido já está cancelado');

            if ($request->status !== CollectionRequestStatus::ACCEPTED) throw new ConflictHttpException('Este pedido não foi aceito e não está disponível para coleta.');

            $oldStatus = $request->status;
            $withdrawalDate = now();

            $dataForUpdate = $this->buildCollectionUpdateData(
                provider: $provider,
                status: CollectionRequestStatus::COLLECTED
            );

            $dataForUpdate['withdrawal_date'] = $withdrawalDate;

            $this->collectionRequestRepository->update(
                $request,
                $dataForUpdate
            );

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::COLLECTED,
                provider: $provider,
                acceptanceDate: null,
                withdrawalDate: $withdrawalDate,
                completionDate: null,
                canceledDate: null
            );

            return $request->refresh();
        });
    }

    public function complete(
        User $user, array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $provider = $this->resolveCollectionProvider(
                $user,
                $data['company_id'] ?? null
            );

            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ((int) $request->requester_user_id !== (int) $user->id) throw new AuthorizationException('Este pedido não aceito por este prestador.');

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new CollectionCanceledExcpetion('Este pedido já está cancelado');

            if ($request->status !== CollectionRequestStatus::COLLECTED) throw new ConflictHttpException('Este pedido não foi aceito e não pode ser concluído.');

            $oldStatus = $request->status;
            $completionDate = now();

            $dataForUpdate = $this->buildCollectionUpdateData(
                provider: $provider,
                status: CollectionRequestStatus::COMPLETED
            );

            $dataForUpdate['completion_date'] = $completionDate;

            $this->collectionRequestRepository->update(
                $request,
                $dataForUpdate
            );

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::COMPLETED,
                provider: $provider,
                acceptanceDate: null,
                withdrawalDate: null,
                completionDate: $completionDate,
                canceledDate: null
            );

            return $request->refresh();
        });
    }

    public function cancel(
        User $user, array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $provider = $this->resolveCollectionProvider(
                $user,
                $data['company_id'] ?? null
            );

            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ((int) $request->requester_user_id !== (int) $user->id) throw new AuthorizationException('Este pedido não aceito por este prestador.');

            if ($request->status !== CollectionRequestStatus::OPEN) throw new ConflictHttpException('Somente pedidos abertos podem ser caneclados.');

            $oldStatus = $request->status;
            $canceledDate = now();

            $dataForUpdate = $this->buildCollectionUpdateData(
                provider: $provider,
                status: CollectionRequestStatus::CANCELLED
            );

            $dataForUpdate['canceled_date'] = $canceledDate;

            $this->collectionRequestRepository->update(
                $request,
                $dataForUpdate
            );

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::CANCELLED,
                provider: $provider,
                acceptanceDate: null,
                withdrawalDate: null,
                completionDate: null,
                canceledDate: $canceledDate
            );

            return $request->refresh();
        });
    }

}
