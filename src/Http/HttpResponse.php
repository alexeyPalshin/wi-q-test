<?php

namespace GreatFood\Http;

final class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        /** @var array<string, string> */
        public readonly array $headers,
        public readonly string $body
    ) {}

    public function json(): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($this->body, true);

        return is_array($data) ? $data : [];
    }
}