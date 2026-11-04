<?php

namespace Diana\Database\Drivers;

use Diana\Database\Contracts\Connection;
use PDO;

class PDOConnection implements Connection
{
    protected ?PDO $pdo;

    public function __construct(#[\SensitiveParameter] array $data)
    {
        $this->pdo = PDO::connect(
            $data['dsn'],
            $data['username'] ?? null,
            $data['password'] ?? null,
            $data['options'] ?? []
        );
    }

    public function read(string $query, array $params = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function write(string $query, array $params = []): void
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
    }

    public function disconnect(): void
    {
        // note: the connection should not be closed manually, as PHP does it automatically
        $this->pdo = null;
    }
}