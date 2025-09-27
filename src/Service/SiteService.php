<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\RepositoryFactory;

final class SiteService
{
    public function __construct(private RepositoryFactory $factory)
    {
    }

    public function siteSettings(): array
    {
        $settings = $this->factory->forEntity('settings')->findAll();
        $grouped = [];
        foreach ($settings as $setting) {
            $key = $setting['setting_key'];
            $grouped[$key] = $setting['setting_value'];
        }

        return $grouped;
    }

    public function published(string $entity, int $limit = 10): array
    {
        $repository = $this->factory->forEntity($entity);
        $stmt = $repository->connection()->prepare("SELECT * FROM {$entity} WHERE status = :status ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':status', 'published');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findBySlug(string $entity, string $slug): ?array
    {
        $repository = $this->factory->forEntity($entity);
        $stmt = $repository->connection()->prepare("SELECT * FROM {$entity} WHERE slug = :slug AND status = :status LIMIT 1");
        $stmt->execute(['slug' => $slug, 'status' => 'published']);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function commentsFor(string $type, int $relatedId): array
    {
        $repository = $this->factory->forEntity('comments');
        $stmt = $repository->connection()->prepare('SELECT * FROM comments WHERE related_type = :type AND related_id = :id AND status = :status ORDER BY created_at DESC');
        $stmt->execute(['type' => $type, 'id' => $relatedId, 'status' => 'approved']);

        return $stmt->fetchAll();
    }
}
