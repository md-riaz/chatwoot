<?php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseTokenService
{
    public function generateToken(array $payload, ?string $token = null): string
    {
        $secret = config('app.key');
        return JWT::encode($payload, $secret, 'HS256');
    }

    public function decodeToken(string $token): array
    {
        $secret = config('app.key');

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            // Convert to associative array
            return json_decode(json_encode($decoded), true) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}
