<?php

namespace App\Http\Requests\CollectionPoint\Store;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CollectionPointWasteCategoryStoreRequest extends ApiFormRequest
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
                'required',
                'array',
                'min:1'
            ],

            'waste_categories.*' => [
                'required',
                'array:waste_category_id',
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
