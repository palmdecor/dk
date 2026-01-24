<?php

declare(strict_types=1);

namespace App\Services;

final class TelegramNotifier
{
    public function __construct(private string $url)
    {
    }

    public function notify(string $message): void
    {
        if ($this->url === '') {
            return;
        }
        $payload = json_encode(['text' => $message], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_exec($ch);
        curl_close($ch);
    }
}
