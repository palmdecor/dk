<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Support\EntityFields;

final class RepositoryFactory
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function forEntity(string $entity): CrudRepository
    {
        $table = $this->tableName($entity);
        $fields = EntityFields::for($entity);

        return new CrudRepository($this->database, $table, $fields);
    }

    private function tableName(string $entity): string
    {
        return match ($entity) {
            'services' => 'services',
            'blogs' => 'blogs',
            'pages' => 'pages',
            'sliders' => 'sliders',
            'faq' => 'faq',
            'comments' => 'comments',
            'messages' => 'messages',
            'settings' => 'settings',
            'users' => 'users',
            default => throw new \InvalidArgumentException('Unknown repository: ' . $entity),
        };
    }
}
