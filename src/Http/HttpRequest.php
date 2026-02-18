<?php

namespace GreatFood\Http;

final class HttpRequest
{
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        /** @var array<string, string> */
        public readonly array $headers = [],
        public readonly ?string $body = null
    ) {}
}
