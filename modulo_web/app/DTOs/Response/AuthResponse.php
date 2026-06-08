<?php

namespace App\DTOs\Response;

readonly class AuthResponse
{
    public function __construct(
        public string               $access_token,
        public string               $token_type,
        public VeterinarianResponse $user,
        public ?WorkstationResponse $workstation = null,
    )
    {
    }

    public function toArray(): array
    {
        return array_filter([
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
            'user' => $this->user->toArray(),
            'workstation' => $this->workstation?->toArray(),
        ], fn($v) => $v !== null);
    }
}
