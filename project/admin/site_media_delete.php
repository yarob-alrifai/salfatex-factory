<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$assetKey = $_GET['asset'] ?? '';
if ($id <= 0 || $assetKey !== 'hero_banner') {
    die(h(admin_t('Некорректный запрос')));
}
$stmt = $pdo->prepare('DELETE FROM site_images WHERE id = :id AND asset_key = :key');
$stmt->execute(['id' => $id, 'key' => $assetKey]);
header('Location: site_media.php');
exit;
