<?php

namespace App\Http\Requests\CollectionPoint\Update;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use App\Support\CollectionAccess;
use Illuminate\Validation\Rule;

class CollectionPointUpdateRequest extends ApiFormRequest
{
    public function __construct(
        protected CollectionOpeningHourUpdateRequest $collectionOpeningHourUpdateRequest,
        protected CollectionPointWasteCategoryUpdateRequest $collectionPointWasteCategoryUpdateRequest
    ){}

    protected function prepareForValidation()
    {
        if (! $this->exists('cep')) return;
        $this->merge([
            'cep' => preg_replace(
                '/\D/',
                '',
                (string) $this->input('cep')
            ),
        ]);
    }
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
            ...$this->collectionOpeningHourUpdateRequest->rules(),
            ...$this->collectionPointWasteCategoryUpdateRequest->rules(),

            'name' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'cep' => [
                'sometimes',
                'string',
                'min:8',
                'max:8'
            ],

            'neighborhood' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'address' => [
                'sometimes',
                'string',
                'max:255'
            ],

            'guidelines' => [
                'sometimes',
                'string'
            ],

            'contact'  => [
                'sometimes',
                'string',
                'max:120'
            ],

            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

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
