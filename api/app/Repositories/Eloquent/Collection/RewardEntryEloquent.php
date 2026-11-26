<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\RewardEntry;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\RewardEntryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RewardEntryEloquent extends BaseEloquent implements RewardEntryInterface
{
    protected function modelClass(): string
    {
        return RewardEntry::class;
    }

    public function balanceFor(string $beneficiaryType, int $beneficiaryId): int
    {
        return (int) $this->forBeneficiary($beneficiaryType, $beneficiaryId)->sum('points');
    }

    public function historyFor(string $beneficiaryType, int $beneficiaryId, int $perPage): LengthAwarePaginator
    {
        return $this->forBeneficiary($beneficiaryType, $beneficiaryId)
            ->join('collection_requests', 'collection_requests.id', '=', 'reward_entries.collection_request_id')
            ->orderByDesc('reward_entries.created_at')
            ->orderByDesc('reward_entries.id')
            ->paginate($perPage, ['reward_entries.*', 'collection_requests.requester_user_id']);
    }

    private function forBeneficiary(string $beneficiaryType, int $beneficiaryId): Builder
    {
        return $this->newQuery()
            ->where('beneficiary_type', $beneficiaryType)
            ->where('beneficiary_id', $beneficiaryId);
    }
}
