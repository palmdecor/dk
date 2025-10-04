<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI use only.');
}

require __DIR__ . '/../includes/bootstrap.php';

$aiConfig = $config['ai'] ?? [];
$endpoint = $aiConfig['blog_endpoint'] ?? ($aiConfig['endpoint'] ?? '');
if (empty($endpoint) || empty($aiConfig['api_key'])) {
    echo "AI blog configuration missing.\n";
    exit(0);
}

$logFile = __DIR__ . '/../storage/logs/ai-posts.log';
$today = date('Y-m-d');
$lastRun = get_setting($pdo, 'ai_blog_last_run');
if ($lastRun === $today) {
    echo "AI blog post already generated today.\n";
    exit(0);
}

$topicsSetting = get_setting($pdo, 'ai_blog_topics', 'kredi notu, finansal planlama, faiz oranları');
$topics = array_values(array_filter(array_map('trim', explode(',', $topicsSetting))));
if (empty($topics)) {
    $topics = ['kredi notu', 'faiz oranları', 'tasarruf ipuçları'];
}
$topic = $topics[array_rand($topics)];

function call_ai_blog_endpoint(string $endpoint, array $aiConfig, string $topic): ?array
{
    $prompt = sprintf('Türkçe yanıt ver. "%s" konusunda finans portalı için güven veren, yaklaşık 500 kelimelik HTML paragraf yapısında bir blog yazısı üret. JSON formatında yanıtla ve yalnızca {"title":"...","excerpt":"...","body":"..."} döndür.', $topic);

    $payload = json_encode([
        'model' => $aiConfig['blog_model'] ?? ($aiConfig['model'] ?? 'gpt-finance-writer'),
        'prompt' => $prompt,
        'max_tokens' => 1200,
        'temperature' => 0.7,
        'language' => 'tr',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . ($aiConfig['api_key'] ?? ''),
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return null;
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        return null;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return null;
    }

    if (isset($decoded['title'], $decoded['body'])) {
        return [
            'title' => trim((string) $decoded['title']),
            'excerpt' => trim((string) ($decoded['excerpt'] ?? '')),
            'body' => trim((string) $decoded['body']),
        ];
    }

    if (isset($decoded['choices'][0]['text'])) {
        $fallback = json_decode($decoded['choices'][0]['text'], true);
        if (isset($fallback['title'], $fallback['body'])) {
            return [
                'title' => trim((string) $fallback['title']),
                'excerpt' => trim((string) ($fallback['excerpt'] ?? '')),
                'body' => trim((string) $fallback['body']),
            ];
        }
    }

    return null;
}

$postData = call_ai_blog_endpoint($endpoint, $aiConfig, $topic);
if (!$postData || $postData['title'] === '' || $postData['body'] === '') {
    file_put_contents($logFile, '[' . date('c') . "] API yanıtı alınamadı: {$topic}\n", FILE_APPEND);
    echo "AI response invalid.\n";
    exit(1);
}

$title = $postData['title'];
$excerpt = $postData['excerpt'] !== '' ? $postData['excerpt'] : mb_substr(strip_tags($postData['body']), 0, 160) . '...';
$body = sanitize_policy_html($postData['body']);
$slug = ensure_unique_slug($pdo, 'posts', slugify($title));

$stmt = $pdo->prepare('INSERT INTO posts (title, slug, excerpt, body, status, published_at, created_at) VALUES (:title, :slug, :excerpt, :body, :status, NOW(), NOW())');
$stmt->execute([
    'title' => $title,
    'slug' => $slug,
    'excerpt' => $excerpt,
    'body' => $body,
    'status' => 'published',
]);

set_setting($pdo, 'ai_blog_last_run', $today);
file_put_contents($logFile, '[' . date('c') . "] Yapay zeka blog içeriği oluşturuldu: {$slug}\n", FILE_APPEND);

echo "AI blog post created with slug {$slug}.\n";
