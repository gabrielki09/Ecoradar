<?php

namespace App\Http\Requests\Collection;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CancelRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collection_request_id' => [
                'required',
                'integer',
                'exists:collection_requests,id'
            ],

            'company_id' => [
                Rule::requiredIf(
                    fn () => $this->user()->profile_type === ProfileTypes::COMPANY
                ),
                'integer',
                'min:1',
            ],

            'cancellation_reason' => [
                'required',
                'string',
                'max:500'
            ]
        ];
    }
}
