<?php

namespace App\Services\Collection;

use App\Enum\ProfileTypes;
use App\Models\Collection\CollectionRequest;
use App\Models\Collection\RewardEntry;
use App\Models\User;
use App\Repositories\Interface\Collection\RewardEntryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RewardPointService
{
    public function __construct(private RewardEntryInterface $entries) {}

    public function creditCompletion(CollectionRequest $request): int
    {
        $companyId = $request->accepted_company_id;
        if (($companyId === null) === ($request->accepted_collector_profile_id === null)) {
            throw new ConflictHttpException('A solicitação precisa ter um único prestador vinculado para ser concluída.');
        }

        if ($companyId !== null) {
            $providerType = RewardEntry::COMPANY;
            $providerId = $companyId;
        } else {
            $providerType = RewardEntry::USER;
            $providerId = $request->acceptedCollectorProfile()->firstOrFail()->user_id;
            if ($providerId === $request->requester_user_id) {
                throw new ConflictHttpException('O solicitante e o coletor estão vinculados à mesma conta. Verifique os vínculos da coleta.');
            }
        }

        $credit = [
            'collection_request_id' => $request->id,
            'reward_type' => RewardEntry::COLLECTION_COMPLETION,
            'points' => Config::integer('rewards.collection_completion_points'),
        ];
        $this->entries->create([...$credit, 'beneficiary_type' => RewardEntry::USER, 'beneficiary_id' => $request->requester_user_id]);
        $this->entries->create([...$credit, 'beneficiary_type' => $providerType, 'beneficiary_id' => $providerId]);

        return $credit['points'];
    }

    public function balance(User $user, ?int $companyId): int
    {
        return $this->entries->balanceFor(...$this->beneficiary($user, $companyId));
    }

    public function history(User $user, ?int $companyId, int $perPage): LengthAwarePaginator
    {
        [$type, $id] = $this->beneficiary($user, $companyId);

        return $this->entries->historyFor($type, $id, $perPage);
    }

    private function beneficiary(User $user, ?int $companyId): array
    {
        if ($user->profile_type === ProfileTypes::COMPANY) {
            $company = resolveProvider($user, $companyId);

            return [RewardEntry::COMPANY, $company->id];
        }
        if ($user->profile_type === ProfileTypes::COLLECTOR) {
            resolveProvider($user);
        }

        return [RewardEntry::USER, $user->id];
    }
}
