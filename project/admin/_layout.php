<?php
require_once __DIR__ . '/../inc/auth.php';

function admin_header(string $title): void
{
    $username = $_SESSION['admin_username'] ?? '';
    echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>" . h($title) . "</title><link rel='stylesheet' href='../public_html/css/styles.css'></head><body><div class='admin-container'>";
    echo "<div class='admin-nav'><strong>" . h($title) . "</strong> | Logged as: " . h($username) . " | <a href='dashboard.php'>Dashboard</a> <a href='contact_edit.php'>Contacts</a> <a href='news_list.php'>News</a> <a href='categories_list.php'>Categories</a> <a href='groups_list.php'>Groups</a> <a href='variants_list.php'>Variants</a> <a href='logout.php'>Logout</a></div><hr>";
}

function admin_footer(): void
{
    echo "</div><script src='../public_html/js/main.js'></script></body></html>";
}
