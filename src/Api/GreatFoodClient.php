<?php

namespace GreatFood\Api;

use GreatFood\Auth\TokenProvider;
use GreatFood\Http\HttpClientInterface;
use GreatFood\Http\HttpRequest;
use GreatFood\Exceptions\ApiException;

final class GreatFoodClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly HttpClientInterface $http,
        private readonly TokenProvider $tokens
    ) {}

    /** @return array<int, array{id:int,name:string}> */
    public function getMenus(): array
    {
        $url = rtrim($this->baseUrl, '/') . '/menus';
        $res = $this->http->send(new HttpRequest(
            'GET',
            $url,
            $this->authHeaders()
        ));

        if ($res->statusCode !== 200) {
            throw new ApiException("GET /menus failed ({$res->statusCode})");
        }

        $data = $res->json();
        $menus = $data['data'] ?? [];
        return array_map(
            fn($m) => ['id' => (int)$m['id'], 'name' => (string)$m['name']],
            $menus
        );
    }

    /** @return array<int, array<string,mixed>> */
    public function getMenuProducts(int $menuId): array
    {
        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/products";
        $res = $this->http->send(new HttpRequest(
            'GET',
            $url,
            $this->authHeaders()
        ));

        if ($res->statusCode !== 200) {
            throw new ApiException("GET /menu/{$menuId}/products failed ({$res->statusCode})");
        }

        $data = $res->json();
        return $data['data'] ?? [];
    }

    /** @return array<string,mixed> */
    public function updateProduct(int $menuId, int $productId, array $product): array
    {
//        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/product/{${'productId'}}";
        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/product/{$productId}";

        $res = $this->http->send(new HttpRequest(
            'PUT',
            $url,
            $this->authHeaders() + ['Content-Type' => 'application/json'],
            json_encode($product, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));

        if ($res->statusCode < 200 || $res->statusCode >= 300) {
            throw new ApiException("PUT /menu/{$menuId}/product/{$productId} failed ({$res->statusCode})");
        }

        return $res->json();
    }

    /** @return array<string,string> */
    private function authHeaders(): array
    {
        $token = $this->tokens->getToken();
        return ['Authorization' => "Bearer {$token}"];
    }
}
