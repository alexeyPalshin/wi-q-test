<?php

namespace GreatFood\Auth;

use GreatFood\Http\HttpClientInterface;
use GreatFood\Http\HttpRequest;
use GreatFood\Exceptions\ApiException;

final class TokenProvider
{
    private ?string $token = null;
    private int $expiresAt = 0;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly HttpClientInterface $http
    ) {}

    public function getToken(): string
    {
        if ($this->token && time() < $this->expiresAt - 30) {
            return $this->token;
        }

        $url = rtrim($this->baseUrl, '/') . '/auth_token';
        $body = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        $res = $this->http->send(new HttpRequest(
            'POST',
            $url,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            $body
        ));

        if ($res->statusCode < 200 || $res->statusCode >= 300) {
            throw new ApiException("Auth failed with status {$res->statusCode}");
        }

        $data = $res->json();
        $accessToken = $data['access_token'] ?? null;
        $expiresIn = (int)($data['expires_in'] ?? 3600);

        if (!$accessToken) {
            throw new ApiException('Auth response missing access_token');
        }

        $this->token = $accessToken;
        $this->expiresAt = time() + $expiresIn;

        return $this->token;
    }
}