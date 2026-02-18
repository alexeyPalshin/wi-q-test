<?php

use GreatFood\Http\CurlHttpClient;
use GreatFood\Http\MockHttpClient;
use GreatFood\Auth\TokenProvider;
use GreatFood\Api\GreatFoodClient;

require __DIR__ . '/../vendor/autoload.php';

$baseUrl = getenv('BASE_URL') ?: 'https://mock.greatfood.local';
$clientId = getenv('CLIENT_ID') ?: '1337';
$clientSecret = getenv('CLIENT_SECRET') ?: '4j3g4gj304gj3';

if (getenv('BASE_URL')) {
    $http = new CurlHttpClient();
} else {
    $http = new MockHttpClient(__DIR__ . '/../tests/fixtures');
}

$tokens = new TokenProvider($baseUrl, $clientId, $clientSecret, $http);
$api = new GreatFoodClient($baseUrl, $http, $tokens);

$menuId = 7;
$productId = 84;

$products = $api->getMenuProducts($menuId);

$current = null;
foreach ($products as $p) {
    if ((int)$p['id'] === $productId) {
        $current = $p;
        break;
    }
}
if (!$current) {
    fwrite(STDERR, "Product {$productId} not found in menu {$menuId}\n");
    exit(1);
}

// Update the product name
$current['name'] = 'Chips';

// PUT the updated product model
$updated = $api->updateProduct($menuId, $productId, $current);

// Proof of success
echo "Update successful.\n";
echo "Updated product payload returned by API:\n";
echo json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";