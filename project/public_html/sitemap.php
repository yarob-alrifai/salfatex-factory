<?php
require_once __DIR__ . '/../inc/helpers.php';
header('Content-Type: application/xml; charset=utf-8');
$urls = [];
$now = date('c');
$urls[] = ['loc' => site_url('index.php'), 'lastmod' => $now];
$urls[] = ['loc' => site_url('products.php'), 'lastmod' => $now];
$urls[] = ['loc' => site_url('news.php'), 'lastmod' => $now];
$urls[] = ['loc' => site_url('contact.php'), 'lastmod' => $now];
$catStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM product_categories');
while ($row = $catStmt->fetch()) {
    $urls[] = [
        'loc' => $row['canonical_url'] ?: site_url('category.php?category=' . $row['slug']),
        'lastmod' => $row['created_at'] ?? $now
    ];
}
$groupStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM product_groups');
while ($row = $groupStmt->fetch()) {
    $urls[] = [
        'loc' => $row['canonical_url'] ?: site_url('group.php?slug=' . $row['slug']),
        'lastmod' => $row['created_at'] ?? $now
    ];
}
$newsStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM news');
while ($row = $newsStmt->fetch()) {
    $urls[] = [
        'loc' => $row['canonical_url'] ?: site_url('news_item.php?slug=' . $row['slug']),
        'lastmod' => $row['created_at'] ?? $now
    ];
}
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($urls as $url) {
    echo '<url>';
    echo '<loc>' . h($url['loc']) . '</loc>';
    if (!empty($url['lastmod'])) {
        echo '<lastmod>' . h(date('c', strtotime($url['lastmod']))) . '</lastmod>';
    }
    echo '</url>';
}
echo '</urlset>';
