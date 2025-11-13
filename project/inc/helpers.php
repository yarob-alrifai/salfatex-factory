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
    echo "<!DOCTYPE html>\n";
    echo "<html lang=\"ru\">\n<head>\n<meta charset=\"UTF-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n<title>{$metaTitle}</title>\n";
    echo "<meta name=\"description\" content=\"{$metaDesc}\">\n<meta name=\"keywords\" content=\"{$metaKeywords}\">\n";
    echo "<link rel=\"stylesheet\" href=\"css/styles.css\">\n</head>\n<body>\n<header class=\"site-header\">\n<div class=\"container\">\n<a class=\"logo\" href=\"index.php\">Фабрика Салфатекс</a>\n<nav>\n<a href=\"products.php\">Продукция</a>\n<a href=\"news.php\">Новости</a>\n<a href=\"contact.php\">Контакты</a>\n</nav>\n</div>\n</header>\n<main class=\"site-main\">";
}

function site_footer(): void
{
    $year = date('Y');
    echo "</main><footer class=\"site-footer\"><div class=\"container\">&copy; {$year} Фабрика бумажных изделий. Все права защищены.</div></footer>";
    echo "<script src=\"js/main.js\"></script>\n</body>\n</html>";
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

function upload_images(array $files, string $destination): array
{
    $uploaded = [];
    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] !== UPLOAD_ERR_OK) {
            continue;
        }
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $fileName = uniqid('img_', true) . '.' . $ext;
        $target = rtrim($destination, '/'). '/' . $fileName;
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        if (move_uploaded_file($files['tmp_name'][$index], $target)) {
            $uploaded[] = $fileName;
        }
    }
    return $uploaded;
}

function allowed_categories(): array
{
    return [
        'napkins' => 'Салфетки',
        'towels' => 'Бумажные полотенца',
        'toilet' => 'Туалетная бумага',
        'cosmetic' => 'Косметические салфетки'
    ];
}

function sanitize_category(?string $value): ?string
{
    $categories = allowed_categories();
    if ($value && array_key_exists($value, $categories)) {
        return $value;
    }
    return null;
}
