<?php

namespace GreatFood\Api;

use GreatFood\Dto\Menu;
use GreatFood\Dto\Product;
use GreatFood\Http\HttpClientInterface;
use GreatFood\Http\HttpRequest;
use GreatFood\Exceptions\ApiException;

final class GreatFoodClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly HttpClientInterface $http,
    ) {}

    /** @return array<int, array{id:int,name:string}> */
    public function getMenus(): array
    {
        $url = rtrim($this->baseUrl, '/') . '/menus';
        $res = $this->http->send(new HttpRequest('GET', $url));

        if ($res->statusCode !== 200) {
            throw new ApiException("GET /menus failed ({$res->statusCode})");
        }

        $data = $res->json();

        $menus = $data['data'] ?? [];

        return array_map(fn($m) => Menu::fromArray($m), $menus);

    }

    /** @return array<int, array<string,mixed>> */
    public function getMenuProducts(int $menuId): array
    {
        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/products";
        $res = $this->http->send(new HttpRequest('GET', $url));

        if ($res->statusCode !== 200) {
            throw new ApiException("GET /menu/{$menuId}/products failed ({$res->statusCode})");
        }

        $data = $res->json();
        $items = $data['data'] ?? [];

        return array_map(fn($p) => Product::fromArray($p), $items);
    }

    public function updateProduct(int $menuId, int $productId, Product $product): Product
    {
//        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/product/{${'productId'}}";
        $url = rtrim($this->baseUrl, '/') . "/menu/{$menuId}/product/{$productId}";

        $res = $this->http->send(new HttpRequest(
            'PUT',
            $url,
            ['Content-Type' => 'application/json'],
            json_encode($product, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));

        if ($res->statusCode < 200 || $res->statusCode >= 300) {
            throw new ApiException("PUT /menu/{$menuId}/product/{$productId} failed ({$res->statusCode})");
        }

        return Product::fromArray($res->json());
    }
}
