<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'email' => 'O campo :attribute deve conter um e-mail válido.',

    'min' => [
        'string' => 'O campo :attribute deve possuir pelo menos :min caracteres.',
    ],

    'max' => [
        'string' => 'O campo :attribute não pode possuir mais de :max caracteres.',
    ],

    'unique' => 'O valor informado para :attribute já está sendo utilizado.',

    'invalid_document' => 'O CPF ou CNPJ informado é inválido.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'document' => 'CPF/CNPJ',
        'password' => 'senha',
    ],
];
