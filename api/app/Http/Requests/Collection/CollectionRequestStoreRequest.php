<?php

namespace App\Http\Requests\Collection;

use App\Enum\Collection\CollectionItemUnit;
use App\Enum\Days\WeekDay;
use App\Http\Requests\ApiFormRequest;
use App\Support\CollectionAccess;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class CollectionRequestStoreRequest extends ApiFormRequest
{
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
            'city_id' => [
                'required',
                'integer',
                'exists:cities,id',
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
                'exists:waste_categories,id',
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
            ],

            'items.*.quantity_unit' => [
                'required',
                Rule::enum(CollectionItemUnit::class)
            ],

            'observation' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000'
            ],

            'scheduled_day' => [
                'required',
                Rule::enum(WeekDay::class)
            ],

            'scheduled_time_start' => [
                'required',
                'date_format:H:i'
            ],

            'scheduled_time_end' => [
                'required',
                'date_format:H:i',
                'after:scheduled_time_start'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.quantity_unit.required' => 'Selecione a unidade de medida do item.',
            'items.*.quantity_unit' => 'Selecione uma unidade de medida da lista.',
        ];
    }
}
