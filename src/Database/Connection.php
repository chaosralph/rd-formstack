<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class Connection
{
    private static ?PDO $pdo = null;

    public static function get(array $config): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        self::$pdo = new PDO(
            $config['dsn'],
            $config['user'],
            $config['pass'],
            $config['options']
        );

        return self::$pdo;
    }
}
