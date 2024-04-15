<?php

namespace Diana\Database\Drivers;

use Diana\Database\Contracts\Connection;
use PDO;

class PDOConnection implements Connection
{
    protected PDO $pdo;

    public function __construct()
    {

    }

    public function connect(array $data)
    {
        $this->pdo = new PDO($data['dsn'], $data['username'], $data['password']);
    }
}