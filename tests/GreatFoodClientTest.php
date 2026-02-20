<?php

namespace GreatFood\Tests;

use GreatFood\Api\GreatFoodClient;
use GreatFood\Auth\TokenProvider;
use GreatFood\Http\AuthenticatedHttpClient;
use PHPUnit\Framework\TestCase;

final class GreatFoodClientTest extends TestCase
{
    private GreatFoodClient $api;

    protected function setUp(): void
    {
        $baseUrl = 'https://mock.greatfood.local';
        $http = new MockGoodFoodHttpClient(__DIR__ . '/fixtures');
        $tokens = new TokenProvider($baseUrl, '1337', '4j3g4gj304gj3', $http);
        $authClient = new AuthenticatedHttpClient($http, $tokens);
        $this->api = new GreatFoodClient($baseUrl, $authClient);
    }

    public function testScenario1ListsProductsFromTakeaway(): void
    {
        $menus = $this->api->getMenus();
        $this->assertNotEmpty($menus, 'Menus should not be empty');

        $takeaway = array_values(array_filter($menus, fn($m) => strcasecmp($m->name, 'Takeaway') === 0))[0] ?? null;

        $this->assertNotNull($takeaway, 'Expected Takeaway menu');

        $menuId = (int)$takeaway->id;

        $products = $this->api->getMenuProducts($menuId);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products, 'Menu should contain at least 1 product');

        foreach ($products as $p) {
            $this->assertTrue(
                property_exists($p, 'id'),
                sprintf('The object should have a %s property.', 'id')
            );
            $this->assertTrue(
                property_exists($p, 'name'),
                sprintf('The object should have a %s property.', 'name')
            );
        }
    }

    public function testScenario2UpdatesAProductSuccessfully(): void
    {
        $menus = $this->api->getMenus();
        $this->assertNotEmpty($menus);

        $menuId = (int)$menus[0]->id;

        $products = $this->api->getMenuProducts($menuId);
        $this->assertNotEmpty($products);

        $product = $products[0];
        $this->assertTrue(
            property_exists($product, 'id'),
            sprintf('The object should have a %s property.', 'id')
        );
        $this->assertTrue(
            property_exists($product, 'name'),
            sprintf('The object should have a %s property.', 'name')
        );

        $productId = (int)$product->id;

        $updatedProduct = $product;
        $updatedProduct->name = $product->name;

        $result = $this->api->updateProduct($menuId, $productId, $updatedProduct);

        $this->assertSame($productId, (int)($result->id ?? 0));
        $this->assertSame($updatedProduct->name, $result->name ?? null);
    }
}