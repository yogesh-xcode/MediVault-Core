<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

use App\Repositories\SessionRepository;
use Illuminate\Support\Facades\Crypt;

use function Pest\Laravel\json;

readonly class AccessTokenService
{
    public function __construct(
        private SessionRepository $sessionRepo,
    ) {}
    public function createAccessToken(User $user): string
    {
        $state =
            [
                'role' => $user->role,
                'username' => $user->username,
                'secret' => Str::random()
            ];

        return Crypt::encryptString(value: json_encode(value: $state));
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

        return cookie(
            name: "access_token",
            value: $accessToken,
            minutes: $minutes,
            secure: $secure,
            sameSite: "Lax"
        );
    }
    public function validateAccessToken(string $access_token_client): bool
    {
        $decryptedState = json_decode(json: Crypt::decryptString(
            payload: $access_token_client
        ), associative: true);

        $access_token_redis = $this->sessionRepo->redis_get_token(
            role: $decryptedState['role'],
            username: $decryptedState['username']
        );

        if ($access_token_client !== $access_token_redis) {
            return false;
        }
        return true;
    }

    public function revokeAccessToken($access_token_client)
    {
        $decryptedState = json_decode(json: Crypt::decryptString(
            payload: $access_token_client
        ), associative: true);

        $this->sessionRepo->redis_delete_token(
            role: $decryptedState['role'],
            username: $decryptedState['username']
        );
    }
}
