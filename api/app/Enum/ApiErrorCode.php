<?php

namespace App\Enum;

enum ApiErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case INVALID_DOCUMENT = 'INVALID_DOCUMENT';
    case INVALID_CREDENTIALS = 'INVALID_CREDENTIALS ';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';

    public function message(): string
    {
        return match ($this)
        {
            self::VALIDATION_ERROR => 'Os dados informados são inválidos.',
            self::INVALID_DOCUMENT => 'O CPF ou CNPJ informado é inválido.',
            self::INVALID_CREDENTIALS => 'E-mail ou senha inválidos.',
            self::RESOURCE_NOT_FOUND => 'O recurso solicitado não foi encontrado.',
        };
    }
}
