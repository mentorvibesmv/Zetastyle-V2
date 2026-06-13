<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('zetastyle_admin');
    session_start();
}

function admin_db(): PDO
{
    $pdo = db();
    if (!$pdo instanceof PDO) {
        http_response_code(500);
        exit('Database connection failed. Import sql/zetastyle.sql and check includes/database.php.');
    }

    return $pdo;
}

function admin_url(string $path = ''): string
{
    return '/ZetaStyle/admin/' . ltrim($path, '/');
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: ' . admin_url('login.php'));
        exit;
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
}

function redirect_with(string $url, string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: ' . $url);
    exit;
}

function flash_message(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function active_admin_link(string $segment): string
{
    return str_contains($_SERVER['REQUEST_URI'], '/admin/' . $segment) ? 'active' : '';
}

function admin_count(string $table, string $where = '1=1'): int
{
    $statement = admin_db()->query("SELECT COUNT(*) FROM {$table} WHERE {$where}");
    return (int) $statement->fetchColumn();
}

function upload_admin_image(string $field, string $folder = 'uploads'): ?string
{
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
    $mime = mime_content_type($_FILES[$field]['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }

    $targetDir = __DIR__ . '/assets/images/' . trim($folder, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
    $targetPath = $targetDir . '/' . $filename;
    move_uploaded_file($_FILES[$field]['tmp_name'], $targetPath);

    return 'admin/assets/images/' . trim($folder, '/') . '/' . $filename;
}

function upload_admin_images(string $field, string $folder = 'products'): array
{
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) {
        return [];
    }

    $paths = [];
    $files = $_FILES[$field];

    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }

        $_FILES['_single_admin_upload'] = [
            'name' => $name,
            'type' => $files['type'][$index],
            'tmp_name' => $files['tmp_name'][$index],
            'error' => $files['error'][$index],
            'size' => $files['size'][$index],
        ];
        $path = upload_admin_image('_single_admin_upload', $folder);
        if ($path !== null) {
            $paths[] = $path;
        }
    }

    unset($_FILES['_single_admin_upload']);
    return $paths;
}

function log_activity(string $action, string $entity, ?int $entityId = null): void
{
    $statement = admin_db()->prepare(
        'INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, ip_address, created_at)
         VALUES (:admin_id, :action, :entity_type, :entity_id, :ip_address, NOW())'
    );
    $statement->execute([
        ':admin_id' => $_SESSION['admin_id'] ?? null,
        ':action' => $action,
        ':entity_type' => $entity,
        ':entity_id' => $entityId,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
}

function order_statuses(): array
{
    return ['Confirmed', 'Packing', 'Dispatched', 'Delivered', 'Cancelled'];
}

function public_image(string $path): string
{
    if ($path === '') {
        return '';
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return '../' . ltrim($path, '/');
}
