<?php

namespace App\Http\Requests\CollectionPoint\Update;

use App\Enum\Days\WeekDay;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class CollectionOpeningHourUpdateRequest extends ApiFormRequest
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
        $rules = [
            'opening_hours' => ['sometimes', 'array'],
            'opening_hours.*' => [
                'required',
                'array:opening_day,opens_at,closes_at,delete',
            ],

            'opening_hours.*.opening_day' => [
                'required',
                Rule::enum(WeekDay::class),
                'distinct'
            ],

            'opening_hours.*.delete' => [
                'sometimes',
                'boolean',
            ],
        ];

        $openingHours = $this->input('opening_hours');

        if (! is_array($openingHours)) return $rules;

        foreach ($openingHours as $index => $openingHour) {
            if (! is_array($openingHour)) continue;

            $deleteField = "opening_hours.{$index}.delete";
            $opensAtField = "opening_hours.{$index}.opens_at";

            $rules[$opensAtField] = [
                "required_unless:{$deleteField},true",
                'date_format:H:i',
            ];

            $rules["opening_hours.{$index}.closes_at"] = [
                "required_unless:{$deleteField},true",
                'date_format:H:i',
                "after:{$opensAtField}"
            ];
        }

        return $rules;
    }
}
