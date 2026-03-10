<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) config('database.driver', 'mysql');
        if ($driver !== 'mysql') {
            throw new \RuntimeException('Only MySQL driver is supported.');
        }

        $host = (string) config('database.host', '127.0.0.1');
        $port = (string) config('database.port', '3306');
        $name = (string) config('database.name', '');
        $charset = (string) config('database.charset', 'utf8mb4');

        if ($name === '') {
            throw new \RuntimeException('DB_DATABASE is required.');
        }

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);

        self::$connection = new PDO(
            $dsn,
            (string) config('database.username', ''),
            (string) config('database.password', ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$connection;
    }
}
