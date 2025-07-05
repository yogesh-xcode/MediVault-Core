<?php

namespace App\DTOs;


use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Illuminate\Contracts\Support\Responsable;

class ReportDTO implements JsonSerializable, Responsable
{
    public function __construct(
        public array  $data,
        public string $status,
        public string $message,
        public int    $code = 200,
    ) {
    }

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
        return response()->json(
            data: $this,
            status: $this->code,
        );
    }
}
