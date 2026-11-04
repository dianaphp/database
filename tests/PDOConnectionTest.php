<?php

namespace Diana\Tests;

use Diana\Database\Drivers\PDOConnection;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

class PDOConnectionTest extends TestCase
{
    public function testReadAndWritePrepareAndExecuteBoundParameters(): void
    {
        $read = $this->createMock(PDOStatement::class);
        $read->expects(self::once())->method('execute')->with(['id' => 7])->willReturn(true);
        $read->expects(self::once())->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn([['name' => 'Diana']]);
        $write = $this->createMock(PDOStatement::class);
        $write->expects(self::once())->method('execute')->with(['name' => "Diana's test"])->willReturn(true);
        $pdo = $this->createMock(PDO::class);
        $pdo->expects(self::exactly(2))->method('prepare')->willReturnCallback(
            static fn(string $query) => match ($query) {
                'SELECT name FROM users WHERE id = :id' => $read,
                'INSERT INTO users (name) VALUES (:name)' => $write,
            }
        );
        $connection = (new ReflectionClass(PDOConnection::class))->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(PDOConnection::class, 'pdo');
        $property->setValue($connection, $pdo);

        self::assertSame([['name' => 'Diana']], $connection->read('SELECT name FROM users WHERE id = :id', ['id' => 7]));
        $connection->write('INSERT INTO users (name) VALUES (:name)', ['name' => "Diana's test"]);
        $connection->disconnect();
        self::assertNull($property->getValue($connection));
    }

    public function testSqliteRoundTrip(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('pdo_sqlite is required for the SQL round-trip test.');
        }
        $connection = new PDOConnection(['dsn' => 'sqlite::memory:']);
        $connection->write('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        $connection->write('INSERT INTO users (id, name) VALUES (:id, :name)', ['id' => 7, 'name' => "Diana's test"]);
        self::assertSame([['name' => "Diana's test"]], $connection->read('SELECT name FROM users WHERE id = :id', ['id' => 7]));
        $connection->write('UPDATE users SET name = :name WHERE id = :id', ['id' => 7, 'name' => 'Updated']);
        self::assertSame([['name' => 'Updated']], $connection->read('SELECT name FROM users'));
    }
}
