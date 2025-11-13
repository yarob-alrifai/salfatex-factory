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
        'site_media.php' => 'Media',
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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css">
        <?php render_admin_theme_assets(); ?>
    </head>
    <body class="admin-body theme-dark">
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
                    <button type="button" class="admin-theme-toggle" data-admin-theme-toggle aria-pressed="true">
                        <span class="admin-theme-toggle__icon" data-admin-theme-icon>🌙</span>
                        <span data-admin-theme-label>الوضع الليلي</span>
                    </button>
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
    <div id="imageCropModal" class="admin-cropper" hidden>
        <div class="admin-cropper__dialog" role="dialog" aria-modal="true" aria-labelledby="imageCropTitle">
            <header class="admin-cropper__header">
                <div>
                    <p class="eyebrow" id="imageCropTitle">قص الصورة</p>
                    <p data-crop-filename class="admin-cropper__filename"></p>
                </div>
                <button type="button" class="admin-cropper__close" data-crop-cancel aria-label="إغلاق">×</button>
            </header>
            <div class="admin-cropper__body">
                <div class="admin-cropper__canvas">
                    <img src="" alt="صورة للقص" data-crop-image>
                </div>
                <p class="admin-cropper__hint">حدد المربع الذي تريد استخدامه ثم اضغط «تأكيد القص».</p>
            </div>
            <footer class="admin-cropper__actions">
                <button type="button" class="btn" data-crop-confirm>تأكيد القص</button>
                <button type="button" class="btn-secondary" data-crop-skip>استخدام القص التلقائي</button>
                <button type="button" class="btn-secondary" data-crop-cancel>إلغاء</button>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script src="../public_html/js/main.js"></script>
    </body>
    </html>
    <?php
}
