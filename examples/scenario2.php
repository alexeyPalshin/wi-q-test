<?php

use GreatFood\Api\GreatFoodClient;
use GreatFood\Auth\TokenProvider;
use GreatFood\Http\AuthenticatedHttpClient;
use GreatFood\Http\CurlHttpClient;
use GreatFood\Tests\MockGoodFoodHttpClient;

require __DIR__ . '/../vendor/autoload.php';

$baseUrl = getenv('BASE_API_URL') ?: 'https://mock.greatfood.local';
$clientId = getenv('CLIENT_ID') ?: '1337';
$clientSecret = getenv('CLIENT_SECRET') ?: '4j3g4gj304gj3';

if (getenv('BASE_API_URL')) {
    $http = new CurlHttpClient();
} else {
    $http = new MockGoodFoodHttpClient(__DIR__ . '/../tests/fixtures');
}

$tokens = new TokenProvider($baseUrl, $clientId, $clientSecret, $http);

$authClient = new AuthenticatedHttpClient($http, $tokens);

$api = new GreatFoodClient($baseUrl, $authClient);

$menuId = 4;
$productId = 3;

$products = $api->getMenuProducts($menuId);

$current = null;
foreach ($products as $p) {
    if ((int)$p->id === $productId) {
        $current = $p;
        break;
    }
}
if (!$current) {
    fwrite(STDERR, "Product {$productId} not found in menu {$menuId}\n");
    exit(1);
}

// Update the product name
$current->name = 'Chips';

// PUT the updated product model
$updated = $api->updateProduct($menuId, $productId, $current);

// Proof of success
echo "Update successful.\n";
echo "Updated product payload returned by API:\n";
echo json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";