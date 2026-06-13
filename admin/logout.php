<?php
require_once __DIR__ . '/bootstrap.php';
log_activity('logout', 'admin', (int) ($_SESSION['admin_id'] ?? 0));
$_SESSION = [];
session_destroy();
header('Location: ' . admin_url('login.php'));
exit;
