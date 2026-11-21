<?php

namespace App\Http\Requests\CollectionPoint\Update;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CollectionPointWasteCategoryUpdateRequest extends ApiFormRequest
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
            'waste_categories' => [
                'sometimes',
                'array',
            ],

            'waste_categories.*' => [
                'required',
                'array:waste_category_id,delete',
            ],

            'waste_categories.*.delete' => [
                'sometimes',
                'boolean',
            ],

            'waste_categories.*.waste_category_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('waste_categories', 'id')
            ],
        ];
    }
}
