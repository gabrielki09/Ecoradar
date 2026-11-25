<?php

namespace App\Http\Requests\Company;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use App\Support\CollectionAccess;
use Illuminate\Validation\Rule;

class CompanyProfileUpdateRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->profile_type === ProfileTypes::COMPANY
            && CollectionAccess::provider($this->user());
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'responsible' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'cep' => ['nullable', 'digits:8'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => ['integer', 'distinct', 'exists:cities,id'],
            'waste_categories' => ['required', 'array', 'min:1'],
            'waste_categories.*' => [
                'integer',
                'distinct',
                Rule::exists('waste_categories', 'id')->where(fn ($query) => $query->where(fn ($scope) => $scope->where('is_system', true)
                    ->orWhere('company_id', $this->route('companyId')))
                ),
            ],
        ];
    }
}
