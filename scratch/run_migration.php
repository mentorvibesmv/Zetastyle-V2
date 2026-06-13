<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
if (!$pdo instanceof PDO) {
    die("Database connection failed.\n");
}

$sqlFile = __DIR__ . '/../sql/migration_v3.sql';
if (!file_exists($sqlFile)) {
    die("Migration SQL file not found at $sqlFile\n");
}

echo "Executing migration SQL...\n";
$sql = file_get_contents($sqlFile);

try {
    // MySQL PDO can execute multi-queries if configured, but let's split by semicolon to be safe and print progress
    // We need to parse statements and execute one by one
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if ($query === '') {
            continue;
        }
        echo "Executing: " . substr($query, 0, 50) . "...\n";
        $pdo->exec($query);
    }
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
