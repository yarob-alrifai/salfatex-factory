<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function site_header(string $title, array $meta = []): void
{
    $metaTitle = h($meta['title'] ?? $title);
    $metaDesc = h($meta['description'] ?? 'Салфатекс Фабрика — производитель бумажной продукции.');
    $metaKeywords = h($meta['keywords'] ?? 'салфетки, бумажные полотенца, туалетная бумага, косметические салфетки');

echo <<<HTML
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$metaTitle}</title>
<meta name="description" content="{$metaDesc}">
<meta name="keywords" content="{$metaKeywords}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: '#0ea5e9',
                    dark: '#0369a1',
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
<a class="contact-floating-btn" href="contact.php" aria-label="اذهب إلى صفحة اتصل بنا">
    <span class="contact-floating-btn__icon" aria-hidden="true">📞</span>
    <span class="contact-floating-btn__label">اتصل بنا</span>
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
    $contents = file_get_contents($tmpName);
    if ($contents === false) {
        return null;
    }

    $mime = detect_uploaded_mime_type($file, $tmpName);

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
    if (!$type) {
        $type = 'application/octet-stream';
    }
    return $type;
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
    $slug = strtolower($slug);
    $stmt = $pdo->prepare('SELECT * FROM product_categories WHERE slug = :slug LIMIT 1');
    $stmt->execute(['slug' => $slug]);
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
