<?php

namespace Diana\Database\Drivers;

use Diana\Database\Contracts\Connection;
use PDO;

class PDOConnection implements Connection
{
    protected ?PDO $pdo;

    public function __construct(private readonly array $data)
    {
        // TODO: check if the password should be removed or at least hidden or something, maybe via shadow class that automatically hides the value so it cannot be displayed
        $this->pdo = new PDO($data['dsn'], $data['username'], $data['password']);
    }

    public function read($query, $params = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function write($query, $params = []): void
    {

    }

    public function disconnect(): void
    {
        // note: the connection should not be closed manually, as PHP does it automatically
        $this->pdo = null;
    }
}