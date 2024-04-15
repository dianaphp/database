<?php

namespace Diana\Database;

use Diana\Database\Contracts\Connection;
use Diana\Database\Drivers\PDOConnection;
use Diana\Database\Exceptions\InvalidDriverException;
use Diana\IO\Kernel;
use Diana\Runtime\Package;

class DatabasePackage extends Package
{
    protected array $connections = [];

    public function __construct(Kernel $kernel)
    {
        $this->loadConfig();
    }

    public function boot(Kernel $kernel)
    {
        // $kernel->runCommand("cache-clear");

        foreach ($this->config as $name => $data) {
            if (!is_a($data['driver'], Connection::class, true))
                throw new InvalidDriverException("Attempted to register a connection [{$name}] using the driver [{$data['driver']}] which does not implement [" . Connection::class . "].");

            $this->connections[$name] = new $data['driver']($data);
        }
    }

    public function getConnection(string $name = 'default'): Connection
    {
        return $this->connections[$name];
    }

    public function getConfigDefault(): array
    {
        return [
            'default' => [
                'driver' => PDOConnection::class,
                'dsn' => 'mysql:host=localhost;dbname=diana',
                'username' => 'root',
                'password' => ''
            ]
        ];
    }

    public function getConfigFile(): string
    {
        return 'database';
    }

    public function getConfigCreate(): bool
    {
        return true;
    }
}