<?php

namespace GreatFood\Tests;

use GreatFood\Http\MockHttpClient;
use GreatFood\Auth\TokenProvider;
use GreatFood\Api\GreatFoodClient;
use PHPUnit\Framework\TestCase;

final class GreatFoodClientTest extends TestCase
{
    private GreatFoodClient $api;

    protected function setUp(): void
    {
        $baseUrl = 'https://mock.greatfood.local';
        $http = new MockHttpClient(__DIR__ . '/fixtures');
        $tokens = new TokenProvider($baseUrl, '1337', '4j3g4gj304gj3', $http);
        $this->api = new GreatFoodClient($baseUrl, $http, $tokens);
    }

    public function testScenario1ListsProductsFromTakeaway(): void
    {
        $menus = $this->api->getMenus();
        $this->assertNotEmpty($menus, 'Menus should not be empty');

        $takeaway = array_values(array_filter($menus, fn($m) => strcasecmp($m['name'], 'Takeaway') === 0))[0] ?? null;

        $this->assertNotNull($takeaway, 'Expected Takeaway menu');

        $menuId = (int)$takeaway['id'];

        $products = $this->api->getMenuProducts($menuId);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products, 'Menu should contain at least 1 product');

        foreach ($products as $p) {
            $this->assertArrayHasKey('id', $p);
            $this->assertArrayHasKey('name', $p);
        }
    }

    public function testScenario2UpdatesAProductSuccessfully(): void
    {
        $menus = $this->api->getMenus();
        $this->assertNotEmpty($menus);

        $menuId = (int)$menus[0]['id'];

        $products = $this->api->getMenuProducts($menuId);
        $this->assertNotEmpty($products);

        $product = $products[0];
        $this->assertArrayHasKey('id', $product);
        $this->assertArrayHasKey('name', $product);

        $productId = (int)$product['id'];

        $updatedProduct = $product;
        $updatedProduct['name'] = $product['name']; // или можно добавить префикс

        $result = $this->api->updateProduct($menuId, $productId, $updatedProduct);

        $this->assertSame($productId, (int)($result['id'] ?? 0));
        $this->assertSame($updatedProduct['name'], $result['name'] ?? null);
    }
}