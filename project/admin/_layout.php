<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/_tailwind.php';

function admin_header(string $title): void
{
    $username = $_SESSION['admin_username'] ?? 'Administrator';
    $currentPage = basename($_SERVER['PHP_SELF'] ?? '');
    $navLinks = [
        'dashboard.php' => 'Dashboard',
        'contact_edit.php' => 'Contacts',
        'news_list.php' => 'News',
        'categories_list.php' => 'Categories',
        'groups_list.php' => 'Groups',
        'variants_list.php' => 'Variants',
        'logout.php' => 'Logout',
    ];

    ?>
    <!DOCTYPE html>
    <html lang="ar">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo h($title); ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../public_html/css/styles.css">
        <?php render_admin_theme_assets(); ?>
    </head>
    <body class="admin-body">
    <div class="admin-app">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span>SF</span>
                <div>
                    <strong>Salfatex</strong>
                    <small>Admin Center</small>
                </div>
            </div>
            <div class="admin-user">
                <p>مرحباً،</p>
                <strong><?php echo h($username); ?></strong>
            </div>
            <nav class="admin-menu">
                <?php foreach ($navLinks as $file => $label):
                    $classes = 'admin-menu__link';
                    if ($currentPage === $file) {
                        $classes .= ' is-active';
                    }
                    if ($file === 'logout.php') {
                        $classes .= ' is-destructive';
                    }
                    ?>
                    <a href="<?php echo h($file); ?>" class="<?php echo h($classes); ?>">
                        <span><?php echo h($label); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <p class="eyebrow">مركز التحكم</p>
                    <h1><?php echo h($title); ?></h1>
                    <p class="subtitle">متابعة المحتوى والتحديثات اليومية</p>
                </div>
                <div class="admin-topbar__meta">
                    <span class="badge badge--online">نشط الآن</span>
                </div>
            </header>
            <section class="admin-panel">
    <?php
}

function admin_footer(): void
{
    ?>
            </section>
        </main>
    </div>
    <script src="../public_html/js/main.js"></script>
    </body>
    </html>
    <?php
}
