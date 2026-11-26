<?php

namespace App\Http\Requests\Collection;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use App\Support\CollectionAccess;

class RewardPointsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return CollectionAccess::requester($this->user()) || CollectionAccess::provider($this->user());
    }

    public function rules(): array
    {
        return [
            'company_id' => [$this->user()->profile_type === ProfileTypes::COMPANY ? 'required' : 'prohibited', 'integer', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
