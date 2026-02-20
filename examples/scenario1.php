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

// 1) Find menu named "Takeaway"
$menus = $api->getMenus();
$takeaway = null;

foreach ($menus as $m) {
    if (strcasecmp($m->name, 'Takeaway') === 0) {
        $takeaway = $m;
        break;
    }
}

if (!$takeaway) {
    fwrite(STDERR, "Menu 'Takeaway' not found\n");
    exit(1);
}
$menuId = (int)$takeaway->id;

// 2) Fetch products for that menu
$products = $api->getMenuProducts($menuId);

// 3) Print table
echo "| ID | Name |\n";
echo "| -- | ---- |\n";

foreach ($products as $p) {
    echo "{$p->id} | {$p->name}\n";
}
