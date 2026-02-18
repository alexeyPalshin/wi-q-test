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
        $takeaway = array_values(array_filter($menus, fn($m) => strcasecmp($m['name'], 'Takeaway') === 0))[0] ?? null;

        $this->assertNotNull($takeaway, 'Expected Takeaway menu');
        $products = $this->api->getMenuProducts((int)$takeaway['id']);

        $namesById = [];
        foreach ($products as $p) {
            $namesById[(int)$p['id']] = (string)$p['name'];
        }

        $this->assertArrayHasKey(4, $namesById);
        $this->assertSame('Burger', $namesById[4]);

        $this->assertArrayHasKey(5, $namesById);
        $this->assertSame('Chips', $namesById[5]);

        $this->assertArrayHasKey(99, $namesById);
        $this->assertSame('Lasagna', $namesById[99]);
    }

    public function testScenario2UpdatesProductName(): void
    {
        $menuId = 7;
        $productId = 84;
        $products = $this->api->getMenuProducts($menuId);

        $product = null;
        foreach ($products as $p) {
            if ((int)$p['id'] === $productId) {
                $product = $p;
                break;
            }
        }
        $this->assertNotNull($product, 'Product 84 should exist in fixtures');

        $product['name'] = 'Chips';
        $updated = $this->api->updateProduct($menuId, $productId, $product);

        $this->assertSame('Chips', $updated['name'] ?? null, 'Name should be updated to Chips');
        $this->assertSame(84, (int)($updated['id'] ?? 0));
    }
}