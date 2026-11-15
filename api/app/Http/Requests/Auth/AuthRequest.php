<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class AuthRequest extends ApiFormRequest
{
    protected function prepareForValidation()
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
            'email' => [
                'required',
                'email',
                'max:255'
            ],

            'password' => [
                'required',
                'string'
            ]
        ];
    }
}
