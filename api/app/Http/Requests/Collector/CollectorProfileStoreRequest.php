<?php

namespace App\Http\Requests\Collector;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectorProfileStoreRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user->profile_type !== ProfileTypes::COLLECTOR) return false;

        abort_if($user->onboarding_completed_at !== null, 409, 'O cadastro já foi concluído.');

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
            'service_description' => [
                'required',
                'string',
                'max:500'
            ],

            'cities' => [
                'required',
                'array',
                'min:1'
            ],

            'cities.*' => [
                'required',
                'integer',
                'distinct',
                'exists:cities,id'
            ],

            'waste_category' => [
                'required',
                'array',
                'min:1'
            ],

            'waste_category.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('waste_categories', 'id')
                    ->where('is_system', true)
            ]
        ];
    }
}
