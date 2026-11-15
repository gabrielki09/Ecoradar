<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interface\UserInterface;

class UserEloquent extends BaseEloquent implements UserInterface
{
    protected function modelClass(): string
    {
        return User::class;
    }
}
