<?php
require_once __DIR__ . '/../inc/helpers.php';

header('Content-Type: application/xml; charset=utf-8');

$sitemapHost = $_SERVER['HTTP_HOST'] ?? 'salfatex.ru';
$now = (new DateTimeImmutable('now'))->format(DATE_ATOM);
$seenUrls = [];
$urls = [];

/**
 * Normalize URL to HTTPS and skip invalid/duplicate entries.
 */
function add_sitemap_url(array &$bucket, array &$seen, ?string $candidate, string $changefreq, float $priority, string $lastmod, string $fallbackHost): void
{
    if (!$candidate) {
        return;
    }
    $candidate = trim($candidate);
    if ($candidate === '') {
        return;
    }

    if (!preg_match('#^https?://#i', $candidate)) {
        $candidate = 'https://' . $fallbackHost . '/' . ltrim($candidate, '/');
    }

    $parts = parse_url($candidate);
    if (!$parts || empty($parts['host'])) {
        return;
    }

    $host = $parts['host'];
    if (in_array($host, ['localhost', '127.0.0.1'], true)) {
        $host = $fallbackHost;
    }

    $loc = 'https://' . $host . ($parts['path'] ?? '/');
    if (!empty($parts['query'])) {
        $loc .= '?' . $parts['query'];
    }

    if (isset($seen[$loc])) {
        return;
    }
    $seen[$loc] = true;

    $priority = max(0.0, min(1.0, $priority));
    $bucket[] = [
        'loc' => $loc,
        'lastmod' => $lastmod,
        'changefreq' => $changefreq,
        'priority' => number_format($priority, 1, '.', '')
    ];
}

function format_lastmod(?string $value, string $fallback): string
{
    if ($value) {
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date(DATE_ATOM, $timestamp);
        }
    }
    return $fallback;
}

add_sitemap_url($urls, $seenUrls, site_url(), 'daily', 1.0, $now, $sitemapHost);
add_sitemap_url($urls, $seenUrls, site_url('products.php'), 'daily', 0.9, $now, $sitemapHost);
add_sitemap_url($urls, $seenUrls, site_url('news.php'), 'daily', 0.7, $now, $sitemapHost);
add_sitemap_url($urls, $seenUrls, site_url('contact.php'), 'monthly', 0.6, $now, $sitemapHost);

$catStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM product_categories ORDER BY sort_order ASC, name');
while ($row = $catStmt->fetch()) {
    $loc = $row['canonical_url'] ?: 'category.php?category=' . urlencode($row['slug']);
    $lastmod = format_lastmod($row['created_at'] ?? null, $now);
    add_sitemap_url($urls, $seenUrls, $loc, 'weekly', 0.8, $lastmod, $sitemapHost);
}

$groupStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM product_groups ORDER BY created_at DESC');
while ($row = $groupStmt->fetch()) {
    $loc = $row['canonical_url'] ?: 'group.php?slug=' . urlencode($row['slug']);
    $lastmod = format_lastmod($row['created_at'] ?? null, $now);
    add_sitemap_url($urls, $seenUrls, $loc, 'weekly', 0.7, $lastmod, $sitemapHost);
}

$newsStmt = $pdo->query('SELECT slug, canonical_url, created_at FROM news ORDER BY created_at DESC');
while ($row = $newsStmt->fetch()) {
    $loc = $row['canonical_url'] ?: 'news_item.php?slug=' . urlencode($row['slug']);
    $lastmod = format_lastmod($row['created_at'] ?? null, $now);
    add_sitemap_url($urls, $seenUrls, $loc, 'monthly', 0.6, $lastmod, $sitemapHost);
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    echo '  <url>';
    echo '<loc>' . h($url['loc']) . '</loc>';
    echo '<lastmod>' . h($url['lastmod']) . '</lastmod>';
    echo '<changefreq>' . h($url['changefreq']) . '</changefreq>';
    echo '<priority>' . h($url['priority']) . '</priority>';
    echo "</url>\n";
}
echo '</urlset>';
