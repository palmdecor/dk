<?php
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

$amount = filter_var($_GET['amount'] ?? '', FILTER_VALIDATE_FLOAT);
$term = filter_var($_GET['term'] ?? '', FILTER_VALIDATE_INT);

if ($amount === false || $term === false || $amount <= 0 || $term <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$monthlyRate = get_interest_rate($pdo, (float) $config['app']['interest_rate']);
$monthlyPayment = calculate_monthly_payment($amount, $term, $monthlyRate);

echo json_encode([
    'amount' => $amount,
    'term' => $term,
    'monthly_payment' => round($monthlyPayment, 2),
    'monthly_rate' => $monthlyRate,
]);
