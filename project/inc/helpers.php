<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

const SALFATEX_ALLOWED_IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const SALFATEX_MAX_IMAGE_BYTES = 5 * 1024 * 1024;

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function site_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $normalizedPath = '/' . ltrim($path, '/');
    if ($basePath && $basePath !== '.') {
        return sprintf('%s://%s%s%s', $scheme, $host, $basePath === '/' ? '' : $basePath, $normalizedPath);
    }
    return sprintf('%s://%s%s', $scheme, $host, $normalizedPath);
}

function current_canonical_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return site_url(ltrim($uri, '/'));
}

function is_assoc_array(array $array): bool
{
    if ($array === []) {
        return false;
    }
    return array_keys($array) !== range(0, count($array) - 1);
}

function csrf_token(string $form = 'default'): string
{
    $key = '_csrf_' . $form;
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$key];
}

function csrf_field(string $form = 'default'): string
{
    $token = csrf_token($form);
    return '<input type="hidden" name="_token" value="' . h($token) . '">';
}

function require_csrf_token(string $form = 'default'): void
{
    $sent = $_POST['_token'] ?? '';
    $key = '_csrf_' . $form;
    $valid = $sent && !empty($_SESSION[$key]) && hash_equals($_SESSION[$key], $sent);
    if (!$valid) {
        http_response_code(419);
        exit('CSRF validation failed.');
    }
}

function sanitize_slug(string $value): string
{
    $value = trim($value);
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
    if ($transliterated !== false) {
        $value = $transliterated;
    }
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');
    if ($value === '') {
        $value = 'item-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
    return $value;
}

function safe_html(?string $html): string
{
    if (!$html) {
        return '';
    }
    $allowed = '<p><br><strong><em><ul><ol><li><a><blockquote><h1><h2><h3><h4><h5><h6><span><img><table><thead><tbody><tr><td><th>';
    $clean = strip_tags($html, $allowed);
    $clean = preg_replace('/javascript:/i', '', $clean) ?? $clean;
    return $clean;
}

function sanitize_iframe_embed(?string $html): string
{
    if (!$html) {
        return '';
    }
    if (!preg_match('/<iframe\b[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<\/iframe>/is', $html, $matches)) {
        return '';
    }
    $src = trim($matches[1]);
    if (!preg_match('#^https://#i', $src)) {
        return '';
    }
    $host = strtolower(parse_url($src, PHP_URL_HOST) ?: '');
    if ($host === '') {
        return '';
    }
    $allowedHosts = [
        'google.com',
        'www.google.com',
        'maps.google.com',
        'yandex.ru',
        'yandex.by',
        'yandex.kz',
        'yandex.com',
        '2gis.ru',
    ];
    $isAllowed = false;
    foreach ($allowedHosts as $allowedHost) {
        $allowedHost = strtolower($allowedHost);
        if ($host === $allowedHost) {
            $isAllowed = true;
            break;
        }
        if (strlen($host) > strlen($allowedHost) && substr($host, -strlen($allowedHost)) === $allowedHost) {
            $prefixIndex = strlen($host) - strlen($allowedHost) - 1;
            if ($prefixIndex >= 0 && $host[$prefixIndex] === '.') {
                $isAllowed = true;
                break;
            }
        }
    }
    if (!$isAllowed) {
        return '';
    }
    $width = '100%';
    $height = '480';
    if (preg_match('/width=["\']?([0-9]{2,4}%?)["\']?/i', $html, $widthMatch)) {
        $candidate = $widthMatch[1];
        if (substr($candidate, -1) === '%' && ctype_digit(rtrim($candidate, '%'))) {
            $width = min(100, max(20, (int)rtrim($candidate, '%'))) . '%';
        } elseif (ctype_digit($candidate)) {
            $width = (string)min(1920, max(200, (int)$candidate));
        }
    }
    if (preg_match('/height=["\']?([0-9]{2,4})["\']?/i', $html, $heightMatch) && ctype_digit($heightMatch[1])) {
        $height = (string)min(1200, max(200, (int)$heightMatch[1]));
    }
    return sprintf(
        '<iframe src="%s" width="%s" height="%s" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>',
        h($src),
        h($width),
        h($height)
    );
}

function maybe_convert_to_webp(?string $source): ?string
{
    if (!$source || !function_exists('imagewebp')) {
        return null;
    }
    $imageData = null;
    if (strpos($source, 'data:') === 0) {
        if (preg_match('/^data:[^;]+;base64,(.+)$/', $source, $matches)) {
            $imageData = base64_decode($matches[1]);
        }
    } else {
        $path = $source;
        if ($source[0] !== '/' && !preg_match('#^https?://#i', $source)) {
            $path = __DIR__ . '/../public_html/' . ltrim($source, '/');
        }
        if (is_file($path)) {
            $imageData = file_get_contents($path);
        }
    }
    if (!$imageData) {
        return null;
    }
    $image = @imagecreatefromstring($imageData);
    if (!$image) {
        return null;
    }
    ob_start();
    imagewebp($image, null, 80);
    $webp = ob_get_clean();
    imagedestroy($image);
    if (!$webp) {
        return null;
    }
    return 'data:image/webp;base64,' . base64_encode($webp);
}

function render_picture(?string $source, string $altText, array $options = []): string
{
    if (!$source) {
        return '';
    }
    $class = $options['class'] ?? '';
    $loading = $options['loading'] ?? 'lazy';
    $decoding = $options['decoding'] ?? 'async';
    $webp = maybe_convert_to_webp($source);
    $attributes = sprintf('alt="%s"%s%s', h($altText), $class ? ' class="' . h($class) . '"' : '', $loading ? ' loading="' . h($loading) . '" decoding="' . h($decoding) . '"' : '');
    $html = '<picture>';
    if ($webp) {
        $html .= '<source type="image/webp" srcset="' . h($webp) . '">';
    }
    $html .= '<img src="' . h($source) . '" ' . $attributes . '>';
    $html .= '</picture>';
    return $html;
}

function site_header(string $title, array $meta = []): void
{
    $metaTitle = h($meta['title'] ?? $title);
    $metaDesc = h($meta['description'] ?? 'Salfatex Factory — современное производство бумажной продукции полного цикла.');
    $metaKeywords = h($meta['keywords'] ?? 'салфетки, бумажная продукция, фабрика');
    $canonical = h($meta['canonical'] ?? current_canonical_url());
    $ogTitle = h($meta['og_title'] ?? $metaTitle);
    $ogDesc = h($meta['og_description'] ?? $metaDesc);
    $ogImage = h($meta['og_image'] ?? '');
    $schemaData = $meta['schema'] ?? [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $metaTitle,
        'description' => $metaDesc,
        'url' => $canonical,
    ];
    $schemas = is_assoc_array((array)$schemaData) ? [$schemaData] : (array)$schemaData;
    echo <<<HTML
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$metaTitle}</title>
<meta name="description" content="{$metaDesc}">
<meta name="keywords" content="{$metaKeywords}">
<link rel="canonical" href="{$canonical}">
<meta property="og:title" content="{$ogTitle}">
<meta property="og:description" content="{$ogDesc}">
<meta property="og:type" content="website">
<meta property="og:url" content="{$canonical}">
HTML;
    if ($ogImage) {
        echo '<meta property="og:image" content="' . $ogImage . '">';
    }
    echo <<<HTML
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{$ogTitle}">
<meta name="twitter:description" content="{$ogDesc}">
HTML;
    if ($ogImage) {
        echo '<meta name="twitter:image" content="' . $ogImage . '">';
    }
    echo <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
window.tailwind = window.tailwind || {};
window.tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#0ea5e9',
                    dark: '#0369a1'
                }
            },
            fontFamily: {
                sans: ['"Inter"', '"Cairo"', 'system-ui', 'sans-serif'],
                display: ['"Cairo"', '"Inter"', 'sans-serif']
            },
            boxShadow: {
                glow: '0 25px 65px rgba(14,165,233,0.15)'
            }
        }
    }
};
</script>
<script src="https://cdn.tailwindcss.com?plugins=typography,forms,aspect-ratio"></script>
<script>
(function() {
    try {
        var storedTheme = localStorage.getItem('salfatex-theme');
        if (storedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    } catch (error) {
        document.documentElement.setAttribute('data-theme', 'light');
    }
})();
</script>
<link rel="stylesheet" href="css/styles.css">
HTML;
    foreach ($schemas as $schema) {
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }
    echo <<<HTML
</head>
<body class="bg-slate-50 text-slate-900 antialiased font-sans">
<header class="site-header sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="container mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <a class="logo text-lg font-semibold tracking-tight text-slate-900" href="index.php">
            <span class="text-brand">Фабрика</span> Салфатекс
        </a>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
            <a class="transition hover:text-slate-900" href="products.php">Продукция</a>
            <a class="transition hover:text-slate-900" href="news.php">Новости</a>
            <a class="transition hover:text-slate-900" href="contact.php">Контакты</a>
            <button class="theme-toggle" type="button" data-theme-toggle aria-pressed="false">
                <span class="theme-toggle__icon" aria-hidden="true"></span>
                <span class="theme-toggle__text" data-theme-toggle-text>Ночной режим</span>
            </button>
            <a class="hidden rounded-full bg-brand px-4 py-2 text-white shadow-glow transition hover:bg-brand-dark md:inline-flex" href="products.php">Каталог</a>
        </nav>
    </div>
</header>
<main class="site-main min-h-screen pb-16">
HTML;
}

function site_footer(): void
{
    $year = date('Y');
    echo <<<HTML
</main>
<footer class="site-footer mt-16 bg-slate-900 text-slate-200">
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-6 py-10 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-lg font-semibold">Фабрика бумажных изделий</p>
            <p class="mt-1 text-sm text-slate-400">Современные линии производства салфеток, полотенец и туалетной бумаги.</p>
        </div>
        <div class="flex flex-wrap gap-3 text-sm font-medium">
            <a class="rounded-full border border-slate-700 px-4 py-2 text-slate-200 transition hover:border-brand hover:text-white" href="products.php">Каталог</a>
            <a class="rounded-full border border-slate-700 px-4 py-2 text-slate-200 transition hover:border-brand hover:text-white" href="news.php">Новости</a>
            <a class="rounded-full border border-slate-700 px-4 py-2 text-slate-200 transition hover:border-brand hover:text-white" href="contact.php">Контакты</a>
        </div>
    </div>
    <div class="border-t border-slate-800 px-6 py-4 text-center text-sm text-slate-400">&copy; {$year} Фабрика бумажных изделий. Все права защищены.</div>
</footer>
<a class="contact-floating-btn" href="contact.php" aria-label="Перейти на страницу контактов">
    <span class="contact-floating-btn__icon" aria-hidden="true">📞</span>
    <span class="contact-floating-btn__label">Связаться</span>
</a>
<script src="js/main.js"></script>
</body>
</html>
HTML;
}

function get_contact_info(PDO $pdo): ?array
{
    $stmt = $pdo->query('SELECT * FROM contact_info LIMIT 1');
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_latest_news(PDO $pdo, int $limit = 3): array
{
    $stmt = $pdo->prepare('SELECT * FROM news ORDER BY created_at DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function upload_images(array $files): array
{
    $uploaded = [];
    $normalized = normalize_uploaded_files($files);
    foreach ($normalized as $file) {
        $dataUri = encode_uploaded_image($file);
        if ($dataUri !== null) {
            $uploaded[] = $dataUri;
        }
    }
    return $uploaded;
}

function upload_single_image(?array $file): ?string
{
    if (!$file) {
        return null;
    }
    return encode_uploaded_image($file);
}

function normalize_uploaded_files(array $files): array
{
    if (empty($files)) {
        return [];
    }
    $names = $files['name'] ?? [];
    $types = $files['type'] ?? [];
    $tmpNames = $files['tmp_name'] ?? [];
    $errors = $files['error'] ?? [];
    $sizes = $files['size'] ?? [];
    if (!is_array($names)) {
        $names = [$names];
        $types = [$types];
        $tmpNames = [$tmpNames];
        $errors = [$errors];
        $sizes = [$sizes];
    }
    $normalized = [];
    foreach ($names as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $types[$index] ?? null,
            'tmp_name' => $tmpNames[$index] ?? null,
            'error' => $errors[$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $sizes[$index] ?? null,
        ];
    }
    return $normalized;
}

function encode_uploaded_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmpName = $file['tmp_name'] ?? '';
    if (!$tmpName || (!is_uploaded_file($tmpName) && !is_file($tmpName))) {
        return null;
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > SALFATEX_MAX_IMAGE_BYTES) {
        return null;
    }
    $mime = detect_uploaded_mime_type($file, $tmpName);
    if (!in_array($mime, SALFATEX_ALLOWED_IMAGE_MIME, true)) {
        return null;
    }
    $contents = file_get_contents($tmpName);
    if ($contents === false) {
        return null;
    }
    return 'data:' . $mime . ';base64,' . base64_encode($contents);
}

function detect_uploaded_mime_type(array $file, string $tmpName): string
{
    $type = $file['type'] ?? null;
    if (!$type && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $type = finfo_file($finfo, $tmpName) ?: null;
            finfo_close($finfo);
        }
    }
    return $type ?: 'application/octet-stream';
}

function news_image_src(?string $image): ?string
{
    if (!$image) {
        return null;
    }
    if (strpos($image, 'data:') === 0) {
        return $image;
    }
    return 'uploads/' . ltrim($image, '/');
}

function get_all_categories(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM product_categories ORDER BY name');
    return $stmt->fetchAll();
}

function get_site_image(string $key): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM site_images WHERE asset_key = :key ORDER BY sort_order DESC, id DESC LIMIT 1');
    $stmt->execute(['key' => $key]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function get_site_images(string $key): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM site_images WHERE asset_key = :key ORDER BY sort_order DESC, id DESC');
    $stmt->execute(['key' => $key]);
    return $stmt->fetchAll();
}

function site_image_src(?string $image): ?string
{
    if (!$image) {
        return null;
    }
    if (strpos($image, 'data:') === 0) {
        return $image;
    }
    return 'uploads/' . ltrim($image, '/');
}

function get_category_options(): array
{
    global $pdo;
    $stmt = $pdo->query('SELECT id, name FROM product_categories ORDER BY name');
    return $stmt->fetchAll();
}

function get_category_by_slug(?string $slug): ?array
{
    global $pdo;
    if (!$slug) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM product_categories WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => strtolower($slug)]);
    $category = $stmt->fetch();
    return $category ?: null;
}

function get_category_by_id(int $id): ?array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM product_categories WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $category = $stmt->fetch();
    return $category ?: null;
}

function get_group_by_slug(?string $slug): ?array
{
    global $pdo;
    if (!$slug) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT pg.*, pc.name AS category_name, pc.slug AS category_slug FROM product_groups pg JOIN product_categories pc ON pg.category_id = pc.id WHERE pg.slug = :slug LIMIT 1');
    $stmt->execute(['slug' => strtolower($slug)]);
    $group = $stmt->fetch();
    return $group ?: null;
}

function get_news_by_slug(?string $slug): ?array
{
    global $pdo;
    if (!$slug) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM news WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => strtolower($slug)]);
    $news = $stmt->fetch();
    return $news ?: null;
}
