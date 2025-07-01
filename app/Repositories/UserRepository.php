<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create($user)
    {
        return User::create(attributes: $user);
    }

    public function getUserByUsername(string $username): User
    {
        return User::where(
            column: 'username',
            operator: $username
        )->first();
    }
}
