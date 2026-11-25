<?php

namespace App\Http\Requests\Collector;

use App\Http\Requests\ApiFormRequest;
use App\Enum\ProfileTypes;
use App\Support\CollectionAccess;
use Illuminate\Validation\Rule;

class CollectorProfileUpdateRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->profile_type === ProfileTypes::COLLECTOR && CollectionAccess::provider($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_description' => [
                'sometimes',
                'nullable',
                'string',
                'max:500'
            ],

            'cities' => [
                'sometimes',
                'nullable',
                'array',
                'min:1'
            ],

            'cities.*.city_id' => [
                'required',
                'distinct',
                'integer',
                'exists:cities,id'
            ],

            'cities.*.delete' => [
                'required',
                'boolean',
            ],

            'waste_categories' => [
                'sometimes',
                'nullable',
                'array',
                'min:1'
            ],

            'waste_categories.*.delete' => [
                'required',
                'boolean',
            ],

            'waste_categories.*.waste_category_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('waste_categories', 'id')
                    ->where('is_system', true)
            ]
        ];
    }
}
