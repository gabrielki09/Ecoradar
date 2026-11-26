<?php

namespace App\Repositories\Interface\Collection;

use App\Repositories\Interface\BaseInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RewardEntryInterface extends BaseInterface
{
    public function balanceFor(string $beneficiaryType, int $beneficiaryId): int;

    public function historyFor(string $beneficiaryType, int $beneficiaryId, int $perPage): LengthAwarePaginator;
}
