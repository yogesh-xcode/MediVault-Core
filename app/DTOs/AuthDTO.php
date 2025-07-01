<?php

namespace App\DTOs;

use App\Http\Resources\UserResource;

use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;

use Symfony\Component\HttpFoundation\Cookie;

class AuthDTO implements JsonSerializable, Responsable
{
    public function __construct(
        public readonly UserResource $data,
        public readonly string $status,
        public readonly string $message,
        public readonly int $code = 200,
        public readonly ?Cookie $cookie = null
    ) {}

    public function jsonSerialize(): array
    {
        return [
            "data" => $this->data,
            "status" => $this->status,
            "message" => $this->message
        ];
    }

    public function toResponse($request): JsonResponse
    {
        $response = response()->json(
            data: $this,
            status: $this->code
        );

        return $this->cookie ?
            $response->cookie(cookie: $this->cookie)
            : $response;
    }
}
