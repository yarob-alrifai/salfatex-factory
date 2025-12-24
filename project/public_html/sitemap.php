<?php
$cacheTtl = 6 * 60 * 60;
$cacheDir = __DIR__ . '/../cache';
$cacheFile = $cacheDir . '/sitemap.xml';

header('Content-Type: application/xml; charset=utf-8');

if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
    readfile($cacheFile);
    exit;
}

define('SALFATEX_DB_ALLOW_FAIL', true);
require_once __DIR__ . '/../inc/helpers.php';

$host = $_SERVER['HTTP_HOST'] ?? 'salfatex.ru';
if (in_array($host, ['localhost', '127.0.0.1'], true)) {
    $host = 'salfatex.ru';
}
$baseUrl = 'https://' . $host;

$now = (new DateTimeImmutable('now'))->format(DATE_ATOM);
$seenUrls = [];
$urls = [];

function normalize_sitemap_url(string $candidate, string $baseUrl): ?string
{
    $candidate = trim($candidate);
    if ($candidate === '') {
        return null;
    }

    if (!preg_match('#^https?://#i', $candidate)) {
        $candidate = rtrim($baseUrl, '/') . '/' . ltrim($candidate, '/');
    }

    $parts = parse_url($candidate);
    if (!$parts || empty($parts['path']) && empty($parts['host'])) {
        return null;
    }

    $path = $parts['path'] ?? '/';
    if ($path === '') {
        $path = '/';
    }

    $normalized = rtrim($baseUrl, '/') . $path;
    if (!empty($parts['query'])) {
        $normalized .= '?' . $parts['query'];
    }

    return $normalized;
}

function add_sitemap_url(array &$bucket, array &$seen, ?string $candidate, string $changefreq, float $priority, string $lastmod, string $baseUrl): void
{
    if (!$candidate) {
        return;
    }

    $loc = normalize_sitemap_url($candidate, $baseUrl);
    if (!$loc) {
        return;
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

function slugify_title(string $value): string
{
    $value = trim($value);
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    if ($transliterated !== false) {
        $value = $transliterated;
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    return $value !== '' ? $value : 'news-item';
}

function unique_slug(string $title, array &$existing): string
{
    $base = slugify_title($title);
    $candidate = $base;
    $suffix = 2;
    while (isset($existing[$candidate])) {
        $candidate = $base . '-' . $suffix;
        $suffix++;
    }
    $existing[$candidate] = true;
    return $candidate;
}

add_sitemap_url($urls, $seenUrls, $baseUrl . '/', 'weekly', 1.0, $now, $baseUrl);
add_sitemap_url($urls, $seenUrls, $baseUrl . '/products.php', 'weekly', 0.9, $now, $baseUrl);
add_sitemap_url($urls, $seenUrls, $baseUrl . '/news.php', 'weekly', 0.7, $now, $baseUrl);
add_sitemap_url($urls, $seenUrls, $baseUrl . '/contact.php', 'yearly', 0.5, $now, $baseUrl);

$categoryFallbackSlugs = [
    'bumazhnye-polotentsa-v-slozhenie',
    'bumazhnye-polotentsa-z-slozhenie',
    'paper-towels',
    'tualetnaya-bumaga',
    'dispensernye-salfetki',
    'bumazhnye-salfetki',
    'salfetki-1-8-slozhenie',
    'kosmeticheskie-salfetki'
];

$categoryRows = [];
if (!empty($pdo)) {
    try {
        $catStmt = $pdo->query('SELECT slug, created_at FROM product_categories ORDER BY sort_order ASC, name');
        while ($row = $catStmt->fetch()) {
            if (!empty($row['slug'])) {
                $categoryRows[] = [
                    'slug' => $row['slug'],
                    'lastmod' => format_lastmod($row['created_at'] ?? null, $now)
                ];
            }
        }
    } catch (Throwable $e) {
        $categoryRows = [];
    }
}

if (!$categoryRows) {
    foreach ($categoryFallbackSlugs as $slug) {
        $categoryRows[] = [
            'slug' => $slug,
            'lastmod' => $now
        ];
    }
}

foreach ($categoryRows as $row) {
    $loc = 'category.php?category=' . urlencode($row['slug']);
    add_sitemap_url($urls, $seenUrls, $loc, 'weekly', 0.8, $row['lastmod'], $baseUrl);
}

$newsFallbackSlugs = [
    'ekologichnye-bumazhnye-produkty-salfatex',
    'professionalnye-gigienicheskie-resheniya-dlya-biznesa',
    'sovremennoe-proizvodstvo-bumazhnoy-produkcii',
    'zapusk-obnovlennoy-lineyki-bumazhnykh-polotenets'
];

$newsRows = [];
if (!empty($pdo)) {
    try {
        $slugColumn = $pdo->query("SHOW COLUMNS FROM news LIKE 'slug'");
        $hasSlug = $slugColumn && $slugColumn->fetch();
        if (!$hasSlug) {
            try {
                $pdo->exec('ALTER TABLE news ADD COLUMN slug VARCHAR(255) NULL');
                $hasSlug = true;
            } catch (Throwable $e) {
                $hasSlug = false;
            }
        }

        $updatedColumn = $pdo->query("SHOW COLUMNS FROM news LIKE 'updated_at'");
        $hasUpdatedAt = $updatedColumn && $updatedColumn->fetch();

        $fields = $hasSlug ? 'id, slug, title, created_at' : 'id, title, created_at';
        if ($hasUpdatedAt) {
            $fields .= ', updated_at';
        }

        $orderBy = $hasUpdatedAt ? 'updated_at DESC, created_at DESC' : 'created_at DESC';
        $newsStmt = $pdo->query("SELECT {$fields} FROM news ORDER BY {$orderBy}");

        $existingSlugs = [];
        while ($row = $newsStmt->fetch()) {
            $slug = $hasSlug ? trim((string)($row['slug'] ?? '')) : '';
            if ($slug === '') {
                $slug = unique_slug((string)($row['title'] ?? 'news'), $existingSlugs);
                if ($hasSlug && !empty($row['id'])) {
                    $updateStmt = $pdo->prepare('UPDATE news SET slug = :slug WHERE id = :id');
                    $updateStmt->execute([
                        ':slug' => $slug,
                        ':id' => $row['id']
                    ]);
                }
            } else {
                $existingSlugs[$slug] = true;
            }

            $timestampSource = $hasUpdatedAt ? ($row['updated_at'] ?? null) : ($row['created_at'] ?? null);
            $newsRows[] = [
                'slug' => $slug,
                'lastmod' => format_lastmod($timestampSource, $now)
            ];
        }
    } catch (Throwable $e) {
        $newsRows = [];
    }
}

if (!$newsRows) {
    foreach ($newsFallbackSlugs as $slug) {
        $newsRows[] = [
            'slug' => $slug,
            'lastmod' => $now
        ];
    }
}

foreach ($newsRows as $row) {
    $loc = 'news_item.php?slug=' . urlencode($row['slug']);
    add_sitemap_url($urls, $seenUrls, $loc, 'monthly', 0.6, $row['lastmod'], $baseUrl);
}

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $url) {
    $xml .= '  <url>';
    $xml .= '<loc>' . h($url['loc']) . '</loc>';
    $xml .= '<lastmod>' . h($url['lastmod']) . '</lastmod>';
    $xml .= '<changefreq>' . h($url['changefreq']) . '</changefreq>';
    $xml .= '<priority>' . h($url['priority']) . '</priority>';
    $xml .= "</url>\n";
}
$xml .= '</urlset>';

if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}
if (is_dir($cacheDir)) {
    file_put_contents($cacheFile, $xml, LOCK_EX);
}

echo $xml;
