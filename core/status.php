<?php

declare(strict_types=1);

enum Status: int
{
    case Passive = 0;
    case Active = 1;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Passive => 'Pasif',
        };
    }
}
