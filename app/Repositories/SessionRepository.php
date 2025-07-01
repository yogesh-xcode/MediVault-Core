<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Redis;

class SessionRepository
{
    public function redis_get_token($role, $username): string
    {
        return Redis::get("{$role}:{$username}");
    }

    public function redis_set_token($role, $username, $access_token): void
    {
        Redis::setex("{$role}:{$username}", 900, $access_token);
    }

    public function redis_delete_token($role, $username): void
    {
        Redis::del("{$role}:{$username}");
    }
}
