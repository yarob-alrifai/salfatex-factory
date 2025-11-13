<?php
require_once __DIR__ . '/../inc/auth.php';

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

    echo "<!DOCTYPE html><html lang='ar'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>" . h($title) . "</title>";
    echo "<link rel='preconnect' href='https://fonts.googleapis.com'><link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>";
    echo "<link href='https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>";
    echo "<link rel='stylesheet' href='../public_html/css/styles.css'></head><body class='admin-body'>";
    echo "<div class='admin-app'>";
    echo "<aside class='admin-sidebar'>";
    echo "<div class='admin-brand'><span>SF</span><div><strong>Salfatex</strong><small>Admin Center</small></div></div>";
    echo "<div class='admin-user'><p>مرحباً،</p><strong>" . h($username) . "</strong></div>";
    echo "<nav class='admin-menu'>";
    foreach ($navLinks as $file => $label) {
        $classes = 'admin-menu__link';
        if ($currentPage === $file) {
            $classes .= ' is-active';
        }
        if ($file === 'logout.php') {
            $classes .= ' is-destructive';
        }
        echo "<a href='" . h($file) . "' class='" . $classes . "'><span>" . h($label) . "</span></a>";
    }
    echo "</nav></aside>";
    echo "<main class='admin-main'>";
    echo "<header class='admin-topbar'><div><p class='eyebrow'>مركز التحكم</p><h1>" . h($title) . "</h1><p class='subtitle'>متابعة المحتوى والتحديثات اليومية</p></div><div class='admin-topbar__meta'><span class='badge badge--online'>نشط الآن</span></div></header>";
    echo "<section class='admin-panel'>";
}

function admin_footer(): void
{
    echo "</section></main></div><script src='../public_html/js/main.js'></script></body></html>";
}
