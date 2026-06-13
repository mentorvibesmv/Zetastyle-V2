<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$errors = [];

if (strlen($name) < 2) {
    $errors[] = 'Please enter your name.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
    $errors[] = 'Please enter a valid phone number.';
}

if (strlen($message) < 10) {
    $errors[] = 'Please include a message with at least 10 characters.';
}

if ($errors !== []) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$pdo = db();

if ($pdo instanceof PDO) {
    $statement = $pdo->prepare(
        'INSERT INTO contact_messages (name, email, phone, message, created_at) VALUES (:name, :email, :phone, :message, NOW())'
    );
    $statement->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message,
    ]);
}

echo json_encode([
    'success' => true,
    'message' => 'Thank you. The ZetaStyle studio will reply shortly.',
]);
