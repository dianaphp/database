<?php

namespace Diana\Database\Contracts;

interface Connection
{
    public function read(string $query, array $params = []): array;

    public function write(string $query, array $params = []): void;

    public function disconnect(): void;
}
