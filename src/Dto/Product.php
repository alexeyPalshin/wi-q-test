<?php

namespace GreatFood\Dto;

final class Product
{
    public function __construct(
        public readonly int $id,
        public string $name
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? ''),
        );
    }

    public function withName(string $name): self
    {
        return new self($this->id, $name);
    }

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}