<?php

namespace App\Http\Requests\Auth;

use App\Enum\ProfileTypes;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthUserStoreRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),

            'email' => Str::lower(
                trim((string) $this->input('email'))
            ),

            'document' => preg_replace(
                '/\D/',
                '',
                (string) $this->input('document')
            ),
        ]);
    }

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
            'name' => [
                'bail',
                'required',
                'max:255',
                'string'
            ],

            'email' => [
                'bail',
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'document' => [
                'bail',
                'required',
                'string',
                'max:11',
                'unique:users,document',
                'cpf'
            ],

            'profile_type' => [
                'required',
                Rule::enum(ProfileTypes::class)
            ],

            'password' => [
                'required',
                'string',
                Password::min(8)
            ]
        ];
    }
}
