<?php
// Interface language only: stored content and customer messages are never translated.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$adminLanguages = ['ar', 'ru', 'en'];
$requestedLanguage = $_GET['lang'] ?? null;
if (is_string($requestedLanguage) && in_array($requestedLanguage, $adminLanguages, true)) {
    $_SESSION['admin_language'] = $requestedLanguage;
    setcookie('salfatex_admin_language', $requestedLanguage, [
        'expires' => time() + 365 * 86400,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
$storedLanguage = $_SESSION['admin_language'] ?? $_COOKIE['salfatex_admin_language'] ?? 'ar';
$GLOBALS['admin_language'] = in_array($storedLanguage, $adminLanguages, true) ? $storedLanguage : 'ar';

function admin_lang(): string
{
    return $GLOBALS['admin_language'];
}

function admin_t(string $text): string
{
    static $catalog;
    if ($catalog === null) {
        $catalog = json_decode(file_get_contents(__DIR__ . '/translations.json'), true, 512, JSON_THROW_ON_ERROR);
    }
    return $catalog[$text][admin_lang()] ?? $text;
}

function admin_language_selector(): void
{
    ?>
    <form method="get" class="admin-language" aria-label="Language / اللغة / Язык">
        <?php foreach ($_GET as $key => $value): ?>
            <?php if ($key !== 'lang' && is_string($value)): ?>
                <input type="hidden" name="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        <label for="admin-language"><?php echo htmlspecialchars(admin_t('Language'), ENT_QUOTES, 'UTF-8'); ?></label>
        <select name="lang" id="admin-language" onchange="this.form.submit()">
            <?php foreach (['ar' => 'العربية', 'ru' => 'Русский', 'en' => 'English'] as $code => $label): ?>
                <option value="<?php echo $code; ?>" <?php echo admin_lang() === $code ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit">OK</button></noscript>
    </form>
    <?php
}


function admin_asset_url(string $path): string
{
    $prefix = defined('SALFATEX_PUBLIC_ADMIN') && SALFATEX_PUBLIC_ADMIN ? '../' : '../public_html/';
    return $prefix . ltrim($path, '/');
}
