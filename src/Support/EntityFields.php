<?php

declare(strict_types=1);

namespace App\Support;

final class EntityFields
{
    private const MAP = [
        'services' => ['title', 'slug', 'excerpt', 'content', 'image', 'status'],
        'blogs' => ['title', 'slug', 'excerpt', 'content', 'image', 'status', 'published_at'],
        'pages' => ['title', 'slug', 'content', 'image', 'status'],
        'sliders' => ['title', 'subtitle', 'button_text', 'button_url', 'image', 'status', 'sort_order'],
        'faq' => ['question', 'answer', 'status', 'sort_order'],
        'comments' => ['author_name', 'author_email', 'content', 'status', 'related_type', 'related_id'],
        'messages' => ['name', 'email', 'phone', 'subject', 'message', 'is_read'],
        'settings' => ['group_key', 'setting_key', 'setting_value'],
        'users' => ['name', 'email', 'password', 'role', 'permissions', 'status'],
    ];

    public static function for(string $entity): array
    {
        if (!array_key_exists($entity, self::MAP)) {
            throw new \InvalidArgumentException(sprintf('Alan tanımlaması bulunamadı: %s', $entity));
        }

        return self::MAP[$entity];
    }
}
