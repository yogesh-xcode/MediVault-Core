<?php

namespace App\DTOs;

use App\Http\Resources\PatientResource;
use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Cookie;

class PatientDTO implements JsonSerializable, Responsable
{
    public function __construct(
        public readonly PatientResource|array $data,
        public readonly string $status = 'success',
        public readonly string $message = '',
        public readonly int $code = 200

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
        return  response()->json(
            data: $this,
            status: $this->code
        );
    }
}
