<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interface\UserInterface;

class UserEloquentRepository extends BaseEloquentRepository implements UserInterface
{
    protected function modelClass(): string
    {
        return User::class;
    }
}
