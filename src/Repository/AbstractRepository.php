<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use PDO;

abstract class AbstractRepository
{
    protected PDO $pdo;
    protected string $table;

    public function __construct(Database $database, string $table)
    {
        $this->pdo = $database->pdo();
        $this->table = $table;
    }

    public function connection(): PDO
    {
        return $this->pdo;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
