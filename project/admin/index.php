<?php
require_once __DIR__ . '/../inc/helpers.php';
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$usernameInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $usernameInput]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'بيانات الدخول غير صحيحة، حاول مرة أخرى.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | Salfatex Factory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public_html/css/styles.css">
</head>
<body class="admin-auth">
<div class="admin-auth__shell">
    <div class="admin-auth__intro">
        <p class="eyebrow">Salfatex Factory</p>
        <h1>مرحباً بعودتك إلى مركز الإدارة</h1>
        <p>تستطيع من هنا متابعة الأخبار والمنتجات والفئات وتحديث محتوى الموقع بسهولة واحترافية.</p>
        <ul class="admin-auth__stats">
            <li>
                <strong>إدارة شاملة</strong>
                <span>تحكم كامل بالمحتوى</span>
            </li>
            <li>
                <strong>دعم سريع</strong>
                <span>تحديثات مباشرة</span>
            </li>
            <li>
                <strong>تجربة عربية</strong>
                <span>واجهة سلسة وحديثة</span>
            </li>
        </ul>
    </div>
    <div class="admin-auth__card">
        <div class="admin-auth__logo" aria-hidden="true">SF</div>
        <div class="admin-auth__heading">
            <p class="eyebrow">لوحة تحكم المسؤول</p>
            <h2>تسجيل الدخول</h2>
            <p>أدخل بياناتك لمتابعة إدارة الموقع.</p>
        </div>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert--error"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post" class="admin-form">
            <label>
                <span>اسم المستخدم</span>
                <input type="text" name="username" value="<?php echo h($usernameInput); ?>" required autocomplete="username">
            </label>
            <label>
                <span>كلمة المرور</span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--primary" type="submit">تسجيل الدخول</button>
        </form>
    </div>
</div>
</body>
</html>
