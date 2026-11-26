<?php

namespace App\Services\Collection;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Enum\ProfileTypes;
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
        protected CollectionHistoryInterface $collectionHistoryRepository,
        protected RewardPointService $rewardPoints
    ) {}

    private function storeInHistory(
        CollectionRequest $request,
        CollectionRequestStatus $oldStatus,
        CollectionRequestStatus $newStatus,
        CollectorProfile|Company|User $operator,
        ?CarbonInterface $acceptanceDate,
        ?CarbonInterface $withdrawalDate,
        ?CarbonInterface $completionDate,
        ?CarbonInterface $canceledDate,
        ?string $cancellationReason = ""
    ): void {
        $this->collectionHistoryRepository->create([
            'collection_request_id' => $request->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'request_operator_type' =>

            $operator instanceof CollectorProfile
                ? ProfileTypes::COLLECTOR
                : ($operator instanceof Company
                    ? ProfileTypes::COMPANY
                    : ProfileTypes::COMMON

                ),

            'request_operator_id' => $operator->id,
            'acceptance_date' => $acceptanceDate ?? null,
            'withdrawal_date' => $withdrawalDate ?? null,
            'completion_date' => $completionDate ?? null,
            'canceled_date' => $canceledDate ?? null,
            'cancellation_reason' => $cancellationReason ?? null
        ]);
    }

    public function all(
        array $parameters
    ) {
        return $this->collectionRequestRepository->all(
            parameters: $parameters
        );
    }

    public function mine(User $user, array $parameters)
    {
        return $this->collectionRequestRepository->mine(
            requesterId: $user->id,
            parameters: $parameters
        );
    }

    public function allForProvider(User $user, array $parameters)
    {
        return $this->collectionRequestRepository->allForProvider(
            provider: resolveProvider(
                user: $user,
                companyId: $parameters['company_id'] ?? null
            ),
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
                    'approximate_quantity' => $item['approximate_quantity'],
                    'quantity_unit' => $item['quantity_unit'],
                ]);
            }

            return $collectionRequest->load('items');
        });
    }

    public function available(
        ?int $companyId,
        User $user,
        array $parameters = [],
    ) {
        $provider = resolveProvider(
            $user,
            $companyId
        );

        return $this->collectionRequestRepository->availableFor(
            cityIds: $provider->cities()
                    ->pluck('city_id')
                    ->all(),

            categoryIds: $provider->wasteCategories()
                    ->pluck('waste_category_id')
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
                'city:id,municipio_tom,municipio_ibge,uf',
                'items.wasteCategory:id,name'
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

    private function acceptanceBlockReason(
        CollectionRequest $request,
        CollectorProfile|Company $provider
    ): ?string {
        if (!$provider->cities()->where('city_id', $request->city_id)->exists()) {
            return 'Este prestador não atende a cidade da solicitação.';
        }

        $requestCategoryIds = $request->items()
            ->pluck('waste_category_id')
            ->unique()
            ->all();

        if ($requestCategoryIds === []) return 'A solicitação não possui itens para coleta.';

        $providerCategoryIds = $provider->wasteCategories()
            ->pluck('waste_category_id')
            ->all();

        $unsupportedCategoryIds = array_diff(
            $requestCategoryIds,
            $providerCategoryIds
        );

        return $unsupportedCategoryIds !== []
            ? 'Este prestador não atende todas as categorias da solicitação.'
            : null;
    }

    public function providerActions(CollectionRequest $request, CollectorProfile|Company $provider): array
    {
        $assigned = $provider instanceof CollectorProfile
            ? $request->accepted_collector_profile_id === $provider->id
            : $request->accepted_company_id === $provider->id;

        return [
            'assigned_to_provider' => $assigned,
            'acceptance_block_reason' => $request->status === CollectionRequestStatus::OPEN
                ? $this->acceptanceBlockReason($request, $provider)
                : null,
        ];
    }

    public function accept(User $user, array $data)
    {
        return DB::transaction(function() use ($user, $data) {
            $provider = resolveProvider(
                $user,
                $data['company_id'] ?? null
            );

            /**
             * @var \App\Models\Collection\CollectionRequest $request
             */
            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new ConflictHttpException('Este pedido já está cancelado');

            if ($request->status !== CollectionRequestStatus::OPEN) throw new ConflictHttpException('Este pedido não está mais disponível.');

            $reason = $this->acceptanceBlockReason($request, $provider);
            if ($reason !== null) throw new ConflictHttpException($reason);

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
                operator: $provider,
                acceptanceDate: $acceptanceDate,
                withdrawalDate: null,
                completionDate: null,
                canceledDate: null
            );

            return $request->refresh();
        });
    }

    public function collected(
        User $user,
        array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $provider = resolveProvider(
                $user,
                $data['company_id'] ?? null
            );

            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            $isAssignedProvider = $provider instanceof CollectorProfile
                ? $request->accepted_collector_profile_id === $provider->id
                : $request->accepted_company_id  === $provider->id;

            if (!$isAssignedProvider) throw new AuthorizationException('Este pedido foi aceito por outro prestador.');

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new ConflictHttpException('Este pedido já está cancelado');

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
                operator: $provider,
                acceptanceDate: null,
                withdrawalDate: $withdrawalDate,
                completionDate: null,
                canceledDate: null
            );

            return $request->refresh();
        });
    }

    public function complete(
        User $user,
        array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $provider = resolveProvider(
                $user,
                $data['company_id'] ?? null
            );

            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ($request->status === CollectionRequestStatus::CANCELLED) throw new ConflictHttpException('Este pedido já está cancelado');

            if ($request->status === CollectionRequestStatus::COMPLETED) throw new ConflictHttpException('Esta coleta já foi concluída.');

            if ($request->status !== CollectionRequestStatus::COLLECTED) throw new ConflictHttpException('Somente pedidos marcados como coletados podem ser concluídos.');

            if (
                $provider instanceof Company &&
                $provider->id !== $request->accepted_company_id
            ) throw new AuthorizationException('Este pedido não foi aceito pela sua empresa.');

            if (
                $provider instanceof CollectorProfile &&
                $provider->id !== $request->accepted_collector_profile_id
            ) throw new AuthorizationException('Este pedido não foi aceito por esse prestador.');

            $oldStatus = $request->status;
            $completionDate = now();

            $this->collectionRequestRepository->update($request, [
                'status' => CollectionRequestStatus::COMPLETED,
                'completion_date' => $completionDate,
            ]);

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::COMPLETED,
                operator: $provider,
                acceptanceDate: null,
                withdrawalDate: null,
                completionDate: $completionDate,
                canceledDate: null
            );

            $points = $this->rewardPoints->creditCompletion($request);

            return ['collection_request' => $request->refresh(), 'points_awarded' => $points];
        });
    }

    public function cancel(
        User $user,
        array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $request = $this->collectionRequestRepository
                ->findByUpdate($data['collection_request_id']);

            if ($request->status !== CollectionRequestStatus::OPEN) throw new ConflictHttpException('Somente pedidos abertos podem ser cancelados.');

            if ($request->requester_user_id !== $user->id) throw new AuthorizationException('Você não pode cancelar este pedido.');

            $oldStatus = $request->status;
            $canceledDate = now();

            $this->collectionRequestRepository->update($request, [
                'status' => CollectionRequestStatus::CANCELLED,
                'canceled_date' => $canceledDate
            ]);

            $this->storeInHistory(
                request: $request,
                oldStatus: $oldStatus,
                newStatus: CollectionRequestStatus::CANCELLED,
                operator: $user,
                acceptanceDate: null,
                withdrawalDate: null,
                completionDate: null,
                canceledDate: $canceledDate,
                cancellationReason: $data['cancellation_reason']
            );

            return $request->refresh();
        });
    }

}
