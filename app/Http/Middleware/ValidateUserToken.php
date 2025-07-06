<?php

namespace App\Http\Middleware;

use App\Repositories\SessionRepository;
use App\Services\AccessTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

readonly class ValidateUserToken
{
    public function __construct(
        private AccessTokenService $accessTokenService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {

        $access_token_client = $request->cookie(key: "access_token");

        if (!$access_token_client) {
            return response()->json(data: [
                'data' => 'None',
                'status' => 'error',
                'message' => 'Request is not validated. Access Token is missing!'
            ], status: 401);
        }

        $isValidToken = $this->accessTokenService->validateAccessToken(access_token_client: $access_token_client);

        if (!$isValidToken) {
            return response()->json(data: [
                'data' => 'None',
                'status' => 'error',
                'message' => 'Request is not validated. Access Token is missing!'
            ], status: 401);
        }

        return $next($request);
    }
}