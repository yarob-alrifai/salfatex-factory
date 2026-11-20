<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/../inc/helpers.php';
require_once __DIR__ . '/_tailwind.php';
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$usernameInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token('admin_login');
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
        $error = admin_t('بيانات الدخول غير صحيحة، حاول مرة أخرى.');
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo admin_lang(); ?>" dir="<?php echo admin_lang() === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h(admin_t('لوحة التحكم')); ?> | Salfatex Factory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo h(admin_asset_url('css/styles.css')); ?>">
    <?php render_admin_theme_assets(); ?>
</head>
<body class="admin-auth">
<div class="admin-auth__shell">
    <div class="admin-auth__intro">
        <p class="eyebrow">Salfatex Factory</p>
        <h1><?php echo h(admin_t('مرحباً بعودتك إلى مركز الإدارة')); ?></h1>
        <p><?php echo h(admin_t('تستطيع من هنا متابعة الأخبار والمنتجات والفئات وتحديث محتوى الموقع بسهولة واحترافية.')); ?></p>
        <ul class="admin-auth__stats">
            <li>
                <strong><?php echo h(admin_t('إدارة شاملة')); ?></strong>
                <span><?php echo h(admin_t('تحكم كامل بالمحتوى')); ?></span>
            </li>
            <li>
                <strong><?php echo h(admin_t('دعم سريع')); ?></strong>
                <span><?php echo h(admin_t('تحديثات مباشرة')); ?></span>
            </li>
            <li>
                <strong><?php echo h(admin_t('تجربة عربية')); ?></strong>
                <span><?php echo h(admin_t('واجهة سلسة وحديثة')); ?></span>
            </li>
        </ul>
    </div>
    <div class="admin-auth__card">
        <?php admin_language_selector(); ?>
        <div class="admin-auth__logo" aria-hidden="true">SF</div>
        <div class="admin-auth__heading">
            <p class="eyebrow"><?php echo h(admin_t('لوحة تحكم المسؤول')); ?></p>
            <h2><?php echo h(admin_t('تسجيل الدخول')); ?></h2>
            <p><?php echo h(admin_t('أدخل بياناتك لمتابعة إدارة الموقع.')); ?></p>
        </div>
        <?php if ($error): ?>
            <div class="admin-alert admin-alert--error"><?php echo h($error); ?></div>
        <?php endif; ?>
        <form method="post" class="admin-form">
            <?php echo csrf_field('admin_login'); ?>
            <label>
                <span><?php echo h(admin_t('اسم المستخدم')); ?></span>
                <input type="text" name="username" value="<?php echo h($usernameInput); ?>" required autocomplete="username">
            </label>
            <label>
                <span><?php echo h(admin_t('كلمة المرور')); ?></span>
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn btn--primary" type="submit"><?php echo h(admin_t('تسجيل الدخول')); ?></button>
        </form>
    </div>
</div>
</body>
</html>
