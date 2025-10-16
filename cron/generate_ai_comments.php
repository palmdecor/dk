<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI use only.');
}

require __DIR__ . '/../includes/bootstrap.php';

$aiConfig = $config['ai'] ?? [];
if (empty($aiConfig['endpoint']) || empty($aiConfig['api_key'])) {
    echo "AI configuration missing.\n";
    exit(0);
}

$logFile = __DIR__ . '/../storage/logs/ai-comments.log';

function call_ai_comment_endpoint(array $aiConfig, string $prompt): ?string
{
    $payload = json_encode([
        'model' => $aiConfig['model'] ?? 'gpt-finance-commentator',
        'prompt' => $prompt,
        'max_tokens' => 200,
        'temperature' => 0.7,
        'language' => 'tr',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init($aiConfig['endpoint']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . ($aiConfig['api_key'] ?? ''),
        ],
        CURLOPT_TIMEOUT => 20,
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
    if (is_array($decoded) && isset($decoded['comment'])) {
        return trim((string) $decoded['comment']);
    }

    if (is_array($decoded) && isset($decoded['choices'][0]['text'])) {
        return trim((string) $decoded['choices'][0]['text']);
    }

    return null;
}

try {
    $pdo->query('SET time_zone = "+00:00"');
} catch (Throwable $e) {
    // Ignore time zone errors
}

$postStmt = $pdo->query("SELECT id, title FROM posts WHERE status = 'published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 10");
$posts = $postStmt->fetchAll();

if (empty($posts)) {
    echo "No published posts found.\n";
    exit(0);
}

$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND author_email = :email AND DATE(created_at) = CURDATE()");
$insertStmt = $pdo->prepare("INSERT INTO comments (post_id, author_name, author_email, body, status, created_at) VALUES (:post_id, :author_name, :author_email, :body, :status, NOW())");

foreach ($posts as $post) {
    $checkStmt->execute([
        'post_id' => $post['id'],
        'email' => 'ai@system.local',
    ]);

    if ((int) $checkStmt->fetchColumn() > 0) {
        continue;
    }

    $prompt = sprintf(
        'Finans blogu için %s başlıklı yazıya 3-4 cümlelik, samimi ve güven veren Türkçe bir yorum yaz. Saygılı ol ve satış içermesin.',
        $post['title']
    );

    $comment = call_ai_comment_endpoint($aiConfig, $prompt);
    if (!$comment) {
        file_put_contents($logFile, '[' . date('c') . "] API yanıtı alınamadı: {$post['id']}\n", FILE_APPEND);
        continue;
    }

    $insertStmt->execute([
        'post_id' => $post['id'],
        'author_name' => 'Finans Asistanı',
        'author_email' => 'ai@system.local',
        'body' => $comment,
        'status' => 'pending',
    ]);

    file_put_contents($logFile, '[' . date('c') . "] Yapay zeka yorumu eklendi: {$post['id']}\n", FILE_APPEND);
}

echo "AI comments task completed.\n";
