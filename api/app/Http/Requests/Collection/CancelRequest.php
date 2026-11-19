<?php

namespace App\Http\Requests\Collection;

use App\Support\CollectionAccess;

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
        return CollectionAccess::requester($this->user());
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

            'cancellation_reason' => [
                'required',
                'string',
                'max:500'
            ]
        ];
    }
}
