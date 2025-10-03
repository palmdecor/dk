<?php
require __DIR__ . '/includes/bootstrap.php';
header('Content-Type: application/json');

$orderId = filter_var($_REQUEST['order_id'] ?? null, FILTER_VALIDATE_INT);
$status = $_REQUEST['status'] ?? '';
$provider = $_REQUEST['provider'] ?? 'paytr';
$reference = trim($_REQUEST['reference'] ?? '');

$allowedStatuses = ['pending', 'processing', 'success', 'failed'];
if (!$orderId || !in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$stmt = $pdo->prepare('SELECT orders.*, products.name AS product_name, products.stock AS product_stock FROM orders JOIN products ON products.id = orders.product_id WHERE orders.id = :id');
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$pdo->beginTransaction();
try {
    $update = $pdo->prepare('UPDATE orders SET payment_status = :status, payment_reference = :reference, payment_provider = :provider, updated_at = NOW() WHERE id = :id');
    $update->execute([
        'status' => $status,
        'reference' => $reference !== '' ? $reference : $order['payment_reference'],
        'provider' => $provider,
        'id' => $orderId,
    ]);

    $event = $pdo->prepare('INSERT INTO order_events (order_id, event, payload, created_at) VALUES (:order_id, :event, :payload, NOW())');
    $event->execute([
        'order_id' => $orderId,
        'event' => 'payment_callback',
        'payload' => json_encode([
            'status' => $status,
            'reference' => $reference,
            'provider' => $provider,
        ]),
    ]);

    if ($status === 'success') {
        $stockUpdate = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - :quantity, 0), updated_at = NOW() WHERE id = :id');
        $stockUpdate->execute(['quantity' => $order['quantity'], 'id' => $order['product_id']]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to update order']);
    exit;
}

$customerEmail = null;
$customerName = null;
if (!empty($order['notes'])) {
    $notes = json_decode($order['notes'], true);
    if (is_array($notes)) {
        $customerEmail = $notes['email'] ?? null;
        $customerName = $notes['name'] ?? null;
    }
}

if (!$customerEmail && !empty($order['user_id'])) {
    $userStmt = $pdo->prepare('SELECT email, name FROM users WHERE id = :id');
    $userStmt->execute(['id' => $order['user_id']]);
    if ($user = $userStmt->fetch()) {
        $customerEmail = $user['email'];
        $customerName = $user['name'];
    }
}

if ($status === 'success' && $customerEmail) {
    $subject = 'Siparişiniz alındı - ' . $order['product_name'];
    $body = sprintf("Merhaba %s,\n\nFindeks raporunuz hazırlanıyor. Sipariş numaranız #%d.\nÖdeme referansı: %s\n\nTeşekkür ederiz.", $customerName ?: 'müşterimiz', $orderId, $reference ?: '');
    $headers = 'From: ' . $config['mail']['from'];
    $sent = @mail($customerEmail, $subject, $body, $headers);
    if (!$sent) {
        $logMessage = sprintf("[%s] Email to %s could not be sent. Body: %s\n", date('Y-m-d H:i:s'), $customerEmail, $body);
        file_put_contents(__DIR__ . '/storage/logs/orders.log', $logMessage, FILE_APPEND);
    }
}

echo json_encode(['success' => true, 'status' => $status]);
