<?php

namespace App\Http\Requests\CollectionPoint\Store;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use App\Support\CollectionAccess;
use Illuminate\Validation\Rule;

class CollectionPointStoreRequest extends ApiFormRequest
{
    public function __construct(
        protected CollectionOpeningHourStoreRequest $collectionOpeningHourStoreRequest,
        protected CollectionPointWasteCategoryStoreRequest $collectionPointWasteCategoryStoreRequest
    ){}

    protected function prepareForValidation()
    {
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
            ...$this->collectionOpeningHourStoreRequest->rules(),
            ...$this->collectionPointWasteCategoryStoreRequest->rules(),

            'name' => [
                'required',
                'string',
                'max:255'
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

            'guidelines' => [
                'required',
                'string'
            ],

            'contact'  => [
                'required',
                'string',
                'max:120'
            ],

            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],

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
