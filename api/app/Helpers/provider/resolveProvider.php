<?php

use App\Enum\ProfileTypes;
use App\Enum\CompanyUsers\RoleUser;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

function resolveProvider(
    User $user,
    ?int $companyId = null,
    bool $responsibleOnly = false
): CollectorProfile|Company {
    if ($user->profile_type === ProfileTypes::COLLECTOR) return $user->collectorProfile ?? throw new AuthorizationException('Perfil do coletor não localizado.');

    if ($user->profile_type === ProfileTypes::COMPANY)
    {
        return $companyId === null
            ? throw new InvalidArgumentException('Perfil condiz com perfil de empresa mas o código da empresa não foi definido')
            : $user->companies()
                ->whereKey($companyId)
                ->when($responsibleOnly, fn ($query) => $query->wherePivot('role', RoleUser::RESPONSIBLE->value))
                ->firstOrFail();

    } else {
        throw new AuthorizationException('Usuário não tem acesso a esse recurso.');
    }
}
