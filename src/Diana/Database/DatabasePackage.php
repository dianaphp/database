<?php

namespace Diana\Database;

use Diana\Database\Contracts\Connection;
use Diana\Database\Exceptions\InvalidDriverException;
use Diana\Config\Config;
use InvalidArgumentException;

class DatabasePackage
{
    protected array $connections = [];

    public function __construct(#[Config('database')] Config $config)
    {
        foreach ($config->all() as $name => $data) {
            if (!is_a($data['driver'], Connection::class, true)) {
                throw new InvalidDriverException(
                    "Attempted to register a connection [{$name}] using the driver [{$data['driver']}] which does not implement [" . Connection::class . "]."
                );
            }

            $this->connections[$name] = new $data['driver']($data);
        }
    }

    public function getConnection(string $name = 'default'): Connection
    {
        return $this->connections[$name]
            ?? throw new InvalidArgumentException("Unknown database connection [{$name}].");
    }
}