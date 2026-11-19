<?php

namespace App\Http\Requests\Collection;

use App\Support\CollectionAccess;

use App\Enum\ProfileTypes;
use App\Http\Requests\Filters\FilterV1Request;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class AvailableCollectionRequestsRequest extends FilterV1Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return CollectionAccess::provider($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),

            'company_id' => [
                Rule::requiredIf(
                    fn () => $this->user()->profile_type === ProfileTypes::COMPANY
                ),
                'integer',
                'min:1',
            ]
        ];
    }
}
