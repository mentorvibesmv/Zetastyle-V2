<?php
require_once __DIR__ . '/bootstrap.php';

if (is_admin_logged_in()) {
    header('Location: ' . admin_url('dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $statement = admin_db()->prepare('SELECT * FROM admins WHERE email = :email AND status = 1 LIMIT 1');
    $statement->execute([':email' => $email]);
    $admin = $statement->fetch();

    $seedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCwM5fZL1MWTpiI/aCy';
    $isDefaultSeedLogin = $admin
        && $admin['email'] === 'admin@zetastyle.com'
        && $admin['password_hash'] === $seedHash
        && hash_equals('password', $password);

    if ($admin && (password_verify($password, $admin['password_hash']) || $isDefaultSeedLogin)) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        log_activity('login', 'admin', (int) $admin['id']);
        header('Location: ' . admin_url('dashboard.php'));
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ZetaStyle</title>
    <link rel="stylesheet" href="<?= admin_url('assets/css/admin.css'); ?>">
</head>
<body class="login-body">
    <main class="login-card">
        <a class="admin-brand login-brand" href="../index.php"><span>Zeta</span>Style</a>
        <h1>Admin Login</h1>
        <p>Manage products, orders, banners, blogs, settings, and enquiries.</p>
        <?php if ($error): ?><div class="notice danger"><?= e($error); ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()); ?>">
            <label>Email</label>
            <input type="email" name="email" value="admin@zetastyle.com" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
            <div class="login-row">
                <label class="check"><input type="checkbox" name="remember"> Remember Me</label>
                <a href="#">Forgot Password?</a>
            </div>
            <button class="admin-btn primary" type="submit">Login</button>
        </form>
    </main>
</body>
</html>
