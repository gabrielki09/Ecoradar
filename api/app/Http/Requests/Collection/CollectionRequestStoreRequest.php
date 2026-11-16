<?php

namespace App\Http\Requests\Collection;

use App\Enum\ProviderType;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CollectionRequestStoreRequest extends ApiFormRequest
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
            'city_id' => [
                'required',
                'integer',
                'exists,cities,id',
            ],

            'cep' => [
                'required',
                'string',
                'min:8',
                'max:8'
            ],

            'neighborhood' => [
                'required',
                'string',
                'max:255'
            ],

            'address' => [
                'required',
                'string',
                'max:255'
            ],

            'items' => [
                'required',
                'array',
                'min:1'
            ],

            'items.*.waste_category_id' => [
                'required',
                'integer',
                'distinct',
                'exists,waste_categories,id',
            ],

            'items.*.description' => [
                'nullable',
                'string',
                'max:500'
            ],

            'items.*.approximate_quantity' => [
                'required',
                'numeric',
                'gt:0'
            ]
        ];
    }
}
