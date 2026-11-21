<?php

namespace App\Http\Requests\Onboarding;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnboardingCompanyStoreRequest extends ApiFormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'company_name' => trim((string) $this->input('company_name')),
            'responsible' => trim((string) $this->input('responsible')),
            'neighborhood' => trim((string) $this->input('neighborhood')),
            'address' => trim((string) $this->input('address')),

            'contact_email' => $this->input('contact_email')
                ? Str::lower(trim((string) $this->input('contact_email')))
                : null
            ,

            'document' => preg_replace(
                '/\D/',
                '',
                (string) $this->input('document')
            ),

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
            'company_name' => [
                'required',
                'string',
                'max:255'
            ],

            'document' => [
                'required',
                'string',
                'size:14',
                'cnpj',
                'unique:companies,document'
            ],

            'responsible' => [
                'required',
                'string',
                'max:120'
            ],

            'cep' => [
                'nullable',
                'string',
                'min:8',
                'max:8',
            ],

            'neighborhood' => [
                'nullable',
                'string',
                'max:255'
            ],

            'address' => [
                'nullable',
                'string',
                'max:255'
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:255'
            ],
        ];
    }
}
