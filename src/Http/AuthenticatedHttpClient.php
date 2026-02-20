<?php

namespace GreatFood\Http;

use GreatFood\Auth\TokenProvider;

final class AuthenticatedHttpClient implements HttpClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly TokenProvider $tokens
    ) {
    }

    public function send(HttpRequest $request): HttpResponse
    {
        $withAuth = $this->withAuth($request, $this->tokens->getToken());
        $res = $this->client->send($withAuth);

        if ($res->statusCode === 401) {
            $this->tokens->invalidate();
            $withAuth = $this->withAuth($request, $this->tokens->getToken());
            $res = $this->client->send($withAuth);
        }

        return $res;
    }

    private function withAuth(HttpRequest $request, string $token): HttpRequest
    {
        $headers = $request->headers;
        $headers['Authorization'] = "Bearer {$token}";

        return new HttpRequest(
            $request->method,
            $request->url,
            $headers,
            $request->body
        );
    }
}