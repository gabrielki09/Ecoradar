<?php

namespace App\Http\Requests\CollectionPoint\Store;

use App\Enum\Days\WeekDay;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CollectionOpeningHourStoreRequest extends ApiFormRequest
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
            'opening_hours' => [
                'required',
                'array',
                'min:1'
            ],

            'opening_hours.*' => ['required', 'array:opening_day,opens_at,closes_at'],

            'opening_hours.*.opening_day' => [
                'required',
                Rule::enum(WeekDay::class),
                'distinct'

            ],

            'opening_hours.*.opens_at' => [
                'required',
                'date_format:H:i'
            ],

            'opening_hours.*.closes_at' => [
                'required',
                'date_format:H:i',
                'after:opens_at'
            ],
        ];
    }
}
