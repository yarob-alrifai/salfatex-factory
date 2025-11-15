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
    $defaultBrandName = 'Фабрика Салфатекс';
    $brandName = $defaultBrandName;
    $brandIconSrc = null;
    if (function_exists('site_image_src')) {
        try {
            global $pdo;
            if ($pdo instanceof PDO) {
                $stmt = $pdo->query('SELECT navbar_company_name, navbar_icon FROM contact_info LIMIT 1');
                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row) {
                        if (!empty($row['navbar_company_name'])) {
                            $brandName = trim($row['navbar_company_name']);
                        }
                        $brandIconSrc = site_image_src($row['navbar_icon'] ?? null);
                    }
                }
            }
        } catch (Throwable $exception) {
            $brandIconSrc = null;
        }
    }
    if ($brandName === '') {
        $brandName = $defaultBrandName;
    }
    $brandNameEscaped = h($brandName);
    $brandIconHtml = '';
    if ($brandIconSrc) {
        $brandIconHtml = '<span class="logo__icon" aria-hidden="true"><img src="' . h($brandIconSrc) . '" alt="' . $brandNameEscaped . '"></span>';
    }
    echo <<<HTML
<!DOCTYPE html>
<html lang="ru">
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
            {$brandIconHtml}
            <span class="logo__text">{$brandNameEscaped}</span>
        </a>
        <nav class="flex items-center gap-4 text-sm font-medium text-slate-600">
            <a class="transition hover:text-slate-900" href="products.php">Продукция</a>
            <a class="transition hover:text-slate-900" href="news.php">Новости</a>
            <a class="transition hover:text-slate-900" href="contact.php">Контакты</a>
        </nav>
    </div>
</header>
<main class="site-main min-h-screen pb-16">
HTML;
}

function site_footer(): void
{
    global $pdo;

    $year = date('Y');
    $contact = null;
    if (isset($pdo) && $pdo instanceof PDO) {
        $contact = get_contact_info($pdo);
    }

    $normalizePhoneHref = static function (?string $phone): ?string {
        if (!$phone) {
            return null;
        }
        $normalized = preg_replace('/[^0-9+]/', '', $phone) ?: '';
        if ($normalized === '') {
            return null;
        }
        return 'tel:' . $normalized;
    };

    $phoneMain = trim((string)($contact['phone_main'] ?? '')) ?: null;
    $phoneSecondary = trim((string)($contact['phone_secondary'] ?? '')) ?: null;
    $email = trim((string)($contact['email'] ?? '')) ?: null;
    $address = trim((string)($contact['address'] ?? '')) ?: null;
    $whatsapp = trim((string)($contact['whatsapp_link'] ?? '')) ?: null;
    $telegram = trim((string)($contact['telegram_link'] ?? '')) ?: null;

    $icons = [
        'phone' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg"><path d="M6.65 4.5c-.3-.8-1.05-1.3-1.87-1.2l-1.7.2c-.84.1-1.48.78-1.48 1.63 0 10.26 8.32 18.58 18.58 18.58.85 0 1.53-.64 1.63-1.48l.2-1.7c.1-.82-.4-1.57-1.2-1.87l-3.52-1.34c-.72-.27-1.54-.08-2.08.46l-.83.83a2.38 2.38 0 0 1-2.42.58 15.6 15.6 0 0 1-7.1-7.1 2.38 2.38 0 0 1 .58-2.42l.83-.83c.54-.54.73-1.36.46-2.08Z"/></svg>',
        'mail' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg"><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><path d="m4 7 8 6 8-6"/></svg>',
        'pin' => '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg"><path d="M12 12.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z"/><path d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>'
    ];

    $contactDetails = [];
    if ($phoneMain) {
        $contactDetails[] = [
            'label' => 'Отдел продаж',
            'value' => $phoneMain,
            'href' => $normalizePhoneHref($phoneMain),
            'icon' => $icons['phone'],
        ];
    }
    if ($phoneSecondary) {
        $contactDetails[] = [
            'label' => 'Линия качества',
            'value' => $phoneSecondary,
            'href' => $normalizePhoneHref($phoneSecondary),
            'icon' => $icons['phone'],
        ];
    }
    if ($email) {
        $contactDetails[] = [
            'label' => 'Электронная почта',
            'value' => $email,
            'href' => 'mailto:' . $email,
            'icon' => $icons['mail'],
        ];
    }

    $contactDetailsHtml = '';
    if ($contactDetails) {
        $contactDetailsHtml .= '<ul class="space-y-4">';
        foreach ($contactDetails as $detail) {
            $label = h($detail['label']);
            $value = h($detail['value']);
            $href = $detail['href'] ? h($detail['href']) : null;
            $contactDetailsHtml .= '<li class="flex items-start gap-3">';
            $contactDetailsHtml .= '<span class="mt-1 inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-brand">' . $detail['icon'] . '</span>';
            $contactDetailsHtml .= '<div>';
            $contactDetailsHtml .= '<p class="text-xs uppercase tracking-[0.45em] text-slate-400">' . $label . '</p>';
            if ($href) {
                $contactDetailsHtml .= '<a class="text-lg font-semibold text-white transition hover:text-brand" href="' . $href . '">' . $value . '</a>';
            } else {
                $contactDetailsHtml .= '<p class="text-lg font-semibold text-white">' . $value . '</p>';
            }
            $contactDetailsHtml .= '</div></li>';
        }
        $contactDetailsHtml .= '</ul>';
    }

    $addressHtml = '';
    if ($address) {
        $addressHtml = '<div class="rounded-3xl border border-white/10 bg-white/5 p-5 text-sm text-slate-200 backdrop-blur">'
            . '<p class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.45em] text-slate-400">'
            . $icons['pin'] . '<span>Офис и склад</span></p>'
            . '<p class="text-base font-medium text-white">' . nl2br(h($address), false) . '</p>'
            . '</div>';
    }

    $messengerLinks = [];
    if ($whatsapp) {
        $messengerLinks[] = ['label' => 'WhatsApp', 'href' => $whatsapp];
    }
    if ($telegram) {
        $messengerLinks[] = ['label' => 'Telegram', 'href' => $telegram];
    }

    $messengerHtml = '';
    if ($messengerLinks) {
        $messengerHtml .= '<div class="mt-6 flex flex-wrap gap-3">';
        foreach ($messengerLinks as $link) {
            $messengerHtml .= '<a class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-semibold text-white transition hover:border-brand hover:text-brand" target="_blank" rel="noopener" href="' . h($link['href']) . '">' . h($link['label']) . '</a>';
        }
        $messengerHtml .= '</div>';
    }

    $navLinks = [
        ['label' => 'Главная', 'href' => 'index.php'],
        ['label' => 'Категории', 'href' => 'products.php'],
        ['label' => 'Новости', 'href' => 'news.php'],
        ['label' => 'Контакты', 'href' => 'contact.php'],
    ];
    $navHtml = '<ul class="space-y-3 text-slate-400">';
    foreach ($navLinks as $link) {
        $navHtml .= '<li><a class="transition hover:text-white" href="' . h($link['href']) . '">' . h($link['label']) . '</a></li>';
    }
    $navHtml .= '</ul>';

    $ctaHtml = '<div class="mt-6 flex flex-wrap gap-3">';
    if ($phoneMain && ($mainHref = $normalizePhoneHref($phoneMain))) {
        $ctaHtml .= '<a class="inline-flex items-center justify-center rounded-2xl bg-brand px-5 py-3 text-sm font-semibold text-white shadow-glow transition hover:bg-brand-dark" href="' . h($mainHref) . '">Позвонить сейчас</a>';
    }
    $ctaHtml .= '<a class="inline-flex items-center justify-center rounded-2xl border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:border-brand hover:text-brand" href="contact.php">Отправить заявку</a>';
    $ctaHtml .= '</div>';

    echo <<<HTML
</main>
<section class="global-contact relative isolate overflow-hidden bg-slate-900 text-white">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-70">
        <div class="absolute -top-20 left-1/3 h-72 w-72 rounded-full bg-brand/40 blur-3xl"></div>
        <div class="absolute -bottom-16 right-1/4 h-80 w-80 rounded-full bg-cyan-500/40 blur-3xl"></div>
    </div>
    <div class="relative mx-auto grid max-w-6xl gap-10 px-6 py-16 lg:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <p class="text-sm font-semibold uppercase tracking-[0.55em] text-brand/80">أرسل لنا رسالة</p>
            <h2 class="text-3xl font-bold leading-tight text-white lg:text-4xl">لدينا فريق جاهز للرد على استفساراتك خلال دقائق</h2>
            <p class="text-base text-slate-200">املأ النموذج وأخبرنا بنوعية المنتجات أو الخدمات التي تبحث عنها، وسيقوم مستشارونا بالتواصل معك لتقديم أفضل العروض والحلول اللوجستية.</p>
            <div class="grid gap-4 text-sm text-slate-200 md:grid-cols-2">
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">خطوات سريعة</p>
                    <p class="mt-2 text-lg font-semibold text-white">استشارة مجانية + عرض سعر مخصص</p>
                </div>
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <p class="text-xs uppercase tracking-[0.4em] text-slate-400">تحديثات فورية</p>
                    <p class="mt-2 text-lg font-semibold text-white">متابعة عبر الهاتف أو البريد الإلكتروني</p>
                </div>
            </div>
        </div>
        <div class="rounded-[32px] border border-white/10 bg-white/95 p-8 text-slate-900 shadow-2xl shadow-brand/20">
            <h3 class="text-2xl font-semibold text-slate-900">نموذج طلب التعاون</h3>
            <p class="mt-2 text-sm text-slate-500">سنراجع رسالتك خلال ساعات العمل ونعود إليك بأفضل حل.</p>
            <form class="mt-6 grid gap-4" method="post" action="send_message.php">
HTML;
    echo csrf_field('public_contact');
    echo <<<HTML
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600" for="global-name">الاسم الكامل</label>
                    <input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" id="global-name" name="name" placeholder="مثال: أحمد بن يوسف" required>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-600" for="global-phone">رقم الهاتف</label>
                        <input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" id="global-phone" name="phone" placeholder="00966 50 000 0000" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-600" for="global-email">البريد الإلكتروني</label>
                        <input class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="email" id="global-email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600" for="global-message">أدخل رسالتك</label>
                    <textarea class="min-h-[140px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" id="global-message" name="message" placeholder="حدثنا عن احتياجاتك من المنتجات أو الخدمات اللوجستية" required></textarea>
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-brand px-6 py-3 text-base font-semibold text-white shadow-glow transition hover:bg-brand-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    إرسال الطلب الآن
                    <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 0 1 1.414 0l5 5a1 1 0 0 1 0 1.414l-5 5a1 1 0 0 1-1.414-1.414L13.586 11H4a1 1 0 1 1 0-2h9.586l-3.293-3.293a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <p class="text-xs text-slate-500">بالنقر على إرسال، فإنك توافق على سياسة الخصوصية وشروط الاستخدام الخاصة بسلفاتكس.</p>
            </form>
        </div>
    </div>
</section>
<footer class="site-footer relative overflow-hidden bg-slate-950 text-slate-100">
    <div aria-hidden="true" class="pointer-events-none">
        <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-brand/20 to-transparent"></div>
        <div class="absolute -right-16 top-10 h-48 w-48 rounded-full bg-brand/10 blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 h-56 w-56 rounded-full bg-cyan-500/10 blur-3xl"></div>
    </div>
    <div class="relative mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr]">
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-[0.55em] text-brand/80">Salfatex Factory</p>
                <h3 class="text-3xl font-semibold text-white">Фабрика бумажных изделий полного цикла</h3>
                <p class="text-base text-slate-300">Поставляем салфетки, бумажные полотенца и туалетную бумагу для сетей и HoReCa, поддерживаем гибкие партии и кастомную упаковку.</p>
                {$ctaHtml}
            </div>
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-[0.45em] text-slate-400">Контактные данные</h4>
                <div class="mt-5 space-y-5">
                    {$contactDetailsHtml}
                    {$addressHtml}
                </div>
            </div>
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-[0.45em] text-slate-400">Навигация</h4>
                <div class="mt-5 space-y-5">
                    {$navHtml}
                    {$messengerHtml}
                </div>
            </div>
        </div>
    </div>
    <div class="relative border-t border-white/10 px-6 py-4 text-center text-sm text-slate-400">&copy; {$year} Фабрика бумажных изделий. Все права защищены.</div>
</footer>
<a class="contact-floating-btn" href="contact.php" aria-label="Перейти на страницу контактов">
    <span class="contact-floating-btn__icon" aria-hidden="true">📞</span>
    <span class="contact-floating-btn__label">Связаться</span>
</a>
<script src="js/main.js"></script>
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
