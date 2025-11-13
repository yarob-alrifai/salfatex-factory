<?php
require_once __DIR__ . '/../inc/helpers.php';
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../public_html/css/styles.css">
</head>
<body>
<div class="admin-container">
    <h1>Admin Panel</h1>
    <?php if ($error): ?><div class="alert"><?php echo h($error); ?></div><?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button class="btn" type="submit">Login</button>
    </form>
</div>
</body>
</html>
