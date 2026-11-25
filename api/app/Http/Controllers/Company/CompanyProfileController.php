<?php

namespace App\Http\Controllers\Company;

use App\Enum\ProfileTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CompanyProfileUpdateRequest;
use App\Models\Company\Company;
use App\Support\CollectionAccess;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanyProfileController extends Controller
{
    private function profile(Request $request, int $companyId): Company
    {
        $user = $request->user();
        abort_unless($user->profile_type === ProfileTypes::COMPANY && CollectionAccess::provider($user), 403);

        return resolveProvider($user, $companyId, responsibleOnly: true);
    }

    public function show(Request $request, int $companyId)
    {
        $company = $this->profile($request, $companyId)->load(['cities', 'wasteCategories']);

        return apiSuccess('Dados da empresa.', ['company' => $company]);
    }

    public function update(CompanyProfileUpdateRequest $request, int $companyId)
    {
        $company = $this->profile($request, $companyId);
        $data = $request->validated();

        DB::transaction(function () use ($company, $data) {
            $lockedCompany = Company::query()->lockForUpdate()->findOrFail($company->id);
            $lockedCompany->update(Arr::except($data, ['cities', 'waste_categories']));
            $this->syncCoverage($lockedCompany->cities(), 'city_id', $data['cities']);
            $this->syncCoverage($lockedCompany->wasteCategories(), 'waste_category_id', $data['waste_categories']);
        });

        return apiSuccess('Perfil da empresa atualizado.', [
            'company' => $company->refresh()->load(['cities', 'wasteCategories']),
        ]);
    }

    private function syncCoverage(HasMany $relation, string $key, array $ids): void
    {
        (clone $relation)->whereNotIn($key, $ids)->delete();

        foreach ($ids as $id) {
            $link = (clone $relation)->withTrashed()->firstOrCreate([$key => $id]);
            if ($link->trashed()) {
                $link->restore();
            }
        }
    }
}
