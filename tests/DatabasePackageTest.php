<?php

namespace Diana\Tests;

use Diana\Config\Config;
use Diana\Database\Contracts\Connection;
use Diana\Database\DatabasePackage;
use Diana\Database\Exceptions\InvalidDriverException;
use Diana\Runtime\IlluminateContainer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class DatabasePackageTest extends TestCase
{
    public function testContainerConstructsAndInitializesThePackageWithNamedConfiguration(): void
    {
        $config = new Config('database');
        $config->set('default', ['driver' => TestDatabaseConnection::class, 'dsn' => 'test:default']);
        $config->set('reporting', ['driver' => TestDatabaseConnection::class, 'dsn' => 'test:reporting']);
        $configs = new ReflectionProperty(Config::class, 'configs');
        $previous = $configs->getValue();
        $configs->setValue(null, []);

        try {
            $container = new IlluminateContainer();
            $container->bind(Config::class, static fn() => $config);
            $package = $container->make(DatabasePackage::class);

            self::assertSame('test:default', $package->getConnection()->data['dsn']);
            self::assertSame('test:reporting', $package->getConnection('reporting')->data['dsn']);
            self::assertSame($package->getConnection(), $package->getConnection());
        } finally {
            $configs->setValue(null, $previous);
        }
    }

    public function testInvalidDriverIsRejected(): void
    {
        $config = new Config('database');
        $config->set('default', ['driver' => \stdClass::class]);
        $this->expectException(InvalidDriverException::class);
        new DatabasePackage($config);
    }

    public function testUnknownConnectionHasAnExplicitError(): void
    {
        $package = new DatabasePackage(new Config('database'));
        $this->expectException(InvalidArgumentException::class);
        $package->getConnection('missing');
    }
}

class TestDatabaseConnection implements Connection
{
    public function __construct(public array $data) {}
    public function read(string $query, array $params = []): array { return []; }
    public function write(string $query, array $params = []): void {}
    public function disconnect(): void {}
}
