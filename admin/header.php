<?php
$adminTitle = $adminTitle ?? 'Admin';
$flash = flash_message();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle); ?> | ZetaStyle Admin</title>
    <link rel="stylesheet" href="<?= admin_url('assets/css/admin.css'); ?>">
</head>
<body>
<div class="admin-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <p>Admin Panel</p>
                <h1><?= e($adminTitle); ?></h1>
            </div>
            <div class="admin-user">
                <span><?= e((string) ($_SESSION['admin_name'] ?? 'Admin')); ?></span>
                <a href="<?= admin_url('logout.php'); ?>">Logout</a>
            </div>
        </header>
        <?php if ($flash): ?>
            <div class="notice <?= e($flash['type']); ?>"><?= e($flash['message']); ?></div>
        <?php endif; ?>
