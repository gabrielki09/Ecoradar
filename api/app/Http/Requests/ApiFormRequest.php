<?php

namespace App\Http\Requests;

use App\Enum\ApiErrorCode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $error = ApiErrorCode::VALIDATION_ERROR;

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'code' => $error->value,
                'message' => $error->message(),
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
