<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class DB
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            $config['dsn'],
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
