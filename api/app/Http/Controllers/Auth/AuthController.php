<?php

namespace App\Http\Controllers\Auth;

use App\Enum\ProfileTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\Auth\AuthUserStoreRequest;
use App\Http\Resources\UserResource;
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

    public function login(AuthRequest $req)
    {
        $data = $req->validated();
        return apiSuccess(
            'Login bem sucedido!',
            $this->authUserService->login(
                email: $data['email'],
                password: $data['password']
            ),
            200
        );
    }

    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return apiSuccess(
            'Logout bem suceddio!'
        );
    }

    public function me(Request $req)
    {
        $user = $req->user();
        return apiSuccess(
            'Meus dados',
            [
                'user' => new UserResource($user),
                'onboarding' => [
                    'required' => $user->onboarding_completed_at === null,

                    'next_step' => $user->onboarding_completed_at === null ? match ($user->profile_type) {
                        ProfileTypes::COLLECTOR => 'collector',
                        ProfileTypes::COMPANY => 'company',

                        default => null,
                    } : null
                ]
            ]
        );
    }
}
