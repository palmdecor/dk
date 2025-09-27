<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $dsn = $config['db']['dsn'] ?? '';
        $user = $config['db']['user'] ?? '';
        $password = $config['db']['password'] ?? '';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection error: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }

    public function pdo(): PDO
    {
        return $this->connection;
    }
}
