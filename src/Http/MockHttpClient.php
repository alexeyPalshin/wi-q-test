<?php

namespace GreatFood\Http;

final class MockHttpClient implements HttpClientInterface
{
    /** @var array<string, string> */
    private array $fixtures = [];
    private ?HttpRequest $lastRequest = null;

    public function __construct(
        private readonly string $fixturesDir
    ) {
        $this->fixtures = [
            '/auth_token' => 'token.json',
            '/menus' => 'menus.json',
            '/menu/7/products' => 'menu-products.json',
        ];
    }

    public function send(HttpRequest $request): HttpResponse
    {
        $this->lastRequest = $request;

        $path = parse_url($request->url, PHP_URL_PATH) ?? '/';
        $file = $this->fixtures[$path] ?? null;

        if ($request->method === 'PUT' && preg_match('#^/menu/\d+/product/\d+$#', $path)) {

            return new HttpResponse(
                200,
                ['Content-Type' => 'application/json'],
                $request->body ?? '{}'
            );
        }

        if ($file !== null) {
            $full = rtrim($this->fixturesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
            if (is_file($full)) {
                $body = (string) file_get_contents($full);
                return new HttpResponse(200, ['Content-Type' => 'application/json'], $body);
            }
        }

        return new HttpResponse(404, [], '{"error":"Not found in mock"}');
    }

    public function lastRequest(): ?HttpRequest
    {
        return $this->lastRequest;
    }
}