<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use DateTimeImmutable;

final class CrudRepository extends AbstractRepository
{
    private array $fields;

    public function __construct(Database $database, string $table, array $fields)
    {
        parent::__construct($database, $table);
        $this->fields = $fields;
    }

    public function save(array $data, ?int $id = null): int
    {
        $columns = array_intersect(array_keys($data), $this->fields);
        $filtered = [];

        foreach ($columns as $column) {
            $filtered[$column] = $data[$column];
        }

        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        if ($id === null) {
            $filtered['created_at'] = $now;
            $filtered['updated_at'] = $now;

            $placeholders = ':' . implode(', :', array_keys($filtered));
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->table,
                implode(', ', array_keys($filtered)),
                $placeholders
            );
            $this->pdo->prepare($sql)->execute($filtered);

            return (int) $this->pdo->lastInsertId();
        }

        $filtered['updated_at'] = $now;
        $set = implode(', ', array_map(static fn ($column) => $column . ' = :' . $column, array_keys($filtered)));
        $filtered['id'] = $id;

        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table, $set);
        $this->pdo->prepare($sql)->execute($filtered);

        return $id;
    }
}
