<?php

namespace App\Services\Auth;

use App\Enum\ProfileTypes;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Interface\UserInterface;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthUserService
{
    public function __construct(
        protected UserInterface $userRepository
    ){}

    private function generateToken(User $user): string
    {
        return $user->createToken('web')->plainTextToken;
    }

    private function findByEmail(
        string $email
    ): ?User
    {
        return $this->userRepository->findBy(
            parameters: [
                'email' => $email
            ],
            columns: [
                'id',
                'name',
                'password',
                'email',
                'profile_type',
                'onboarding_completed_at'
            ]
        ) ?? throw new AuthenticationException('Credencias incorretas');
    }

    private function authenticationPayload(User $user): array
    {
        return [
            'user' => new UserResource($user),
            'token' => $this->generateToken($user),
            'token_type' => 'Bearer',

            'onboarding' => [
                'required' => $user->onboarding_completed_at === null,

                'next_step' => match ($user->profile_type) {
                    ProfileTypes::COLLECTOR => 'collector',
                    ProfileTypes::COMPANY => 'company',

                    default => null,
                }
            ]
        ];
    }

    public function login(string $email, string $password)
    {
        $user = $this->findByEmail($email);

        if (! Hash::check($password, $user->password) ) throw new AuthenticationException('Credencias incorretas');

        return $this->authenticationPayload($user);
    }

    public function register(array $data)
    {
        return DB::transaction(function() use ($data) {
            $data['onboarding_completed_at'] = $data['profile_type'] === ProfileTypes::COMMON->value
                ? now()
                : null;

            return $this->authenticationPayload(user: $this->userRepository->create($data));
        });
    }
}
