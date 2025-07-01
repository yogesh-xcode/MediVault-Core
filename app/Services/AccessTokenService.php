<?php

namespace App\Services;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

use App\Repositories\SessionRepository;

readonly class AccessTokenService
{
    public function __construct(
        private SessionRepository $sessionRepo,
    ) {}
    public function createAccessToken(string $state): string
    {
        return $state . Str::random();
    }

    public function setAccessToken(
        string $accessToken,
        string $role,
        string $username,
        int $minutes = 30,
        bool $secure = false,
    ): Cookie {

        $this->sessionRepo->redis_set_token(
            role: $role,
            username: $username,
            access_token: $accessToken
        );

        $token = $this->sessionRepo->redis_get_token(
            role: $role,
            username: $username,
        );

        return cookie(
            name: "access_token",
            value: $accessToken,
            minutes: $minutes,
            secure: $secure,
            sameSite: "Lax"
        );
    }
}
