<?php

namespace Diana\Database;

use Diana\Config\Attributes\Config;
use Diana\Database\Contracts\Connection;
use Diana\Database\Exceptions\InvalidDriverException;
use Diana\Runtime\Kernel;
use Diana\Runtime\Package;
use Illuminate\Container\Container;

class DatabasePackage extends Package
{
    protected array $connections = [];

    public function __construct(#[Config('database')] protected array $config)
    {
    }

    public function init(Kernel $kernel)
    {
        // $kernel->runCommand("cache-clear");
        foreach ($this->config as $name => $data) {
            if (!is_a($data['driver'], Connection::class, true)) {
                throw new InvalidDriverException("Attempted to register a connection [{$name}] using the driver [{$data['driver']}] which does not implement [" . Connection::class . "].");
            }

            $this->connections[$name] = new $data['driver']($data);
        }
    }

    public function getConnection(string $name = 'default'): Connection
    {
        return $this->connections[$name];
    }
}