<?php

namespace App\Http\Requests\CollectionPoint;

use App\Support\CollectionAccess;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use App\Http\Requests\Filters\FilterV1Request;
use Illuminate\Validation\Rule;

class CollectionPointRequest extends ApiFormRequest
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
            ...(new FilterV1Request)->rules(),
            'include_trashed' => ['sometimes', 'boolean'],
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
