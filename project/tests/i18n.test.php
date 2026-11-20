<?php
ini_set('session.save_path', sys_get_temp_dir());
$_GET = ['lang' => $argv[1] ?? 'en', 'id' => '25', 'q' => '<script>alert(1)</script>'];
require __DIR__ . '/../admin/_i18n.php';
function check(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
$expected = in_array($_GET['lang'], ['ar', 'en', 'ru'], true) ? $_GET['lang'] : 'ar';
check(admin_lang() === $expected, 'Language validation failed');
$catalog = json_decode(file_get_contents(__DIR__ . '/../admin/translations.json'), true, 512, JSON_THROW_ON_ERROR);
foreach ($catalog as $key => $row) {
    foreach (['ar', 'ru', 'en'] as $lang) {
        check(isset($row[$lang]) && $row[$lang] !== '', 'Missing translation: ' . $key);
    }
    check(admin_t($key) === $row[$expected], 'Incorrect translation lookup');
}
ob_start();
admin_language_selector();
$html = ob_get_clean();
check(strpos($html, 'value="25"') !== false, 'Message ID lost');
check(strpos($html, '<script>alert') === false, 'Search value not escaped');
check(strpos($html, '&lt;script&gt;') !== false, 'Escaped search missing');
check(strpos($html, 'value="' . $expected . '" selected') !== false, 'Selected language missing');
echo 'PASS: ' . $expected . ' translations, selection, validation and preserved escaped query parameters' . PHP_EOL;
session_destroy();
