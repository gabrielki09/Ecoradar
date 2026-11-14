<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthUserStoreRequest;
use App\Services\Auth\AuthUserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthUserService $authUserService
    ){}

    public function register(AuthUserStoreRequest $req)
    {
        return apiSuccess(
            'Usuário cadastrado com sucesso!',
            $this->authUserService->register($req->validated()),
            201
        );
    }
}
