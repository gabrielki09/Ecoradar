<?php

namespace App\Services\Auth;

use App\Enum\ProfileTypes;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Interface\UserInterface;

class AuthUserService
{
    public function __construct(
        protected UserInterface $userRepository
    ){}

    private function validatePassword(string $password)
    {

    }

    private function generateToken(User $user): string
    {
        return $user->createToken('web')->plainTextToken;
    }

    public function login(string $email, string $password)
    {

    }

    public function register(array $data)
    {
        $data['onboarding_completed_at'] = $data['profile_type'] === ProfileTypes::COMMON->value
            ? now()
            : null;

        $user = $this->userRepository->create($data);

        $token = $this->generateToken($user);

        return [
            'user' => new UserResource($user),
            'token' => $token,
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
}
