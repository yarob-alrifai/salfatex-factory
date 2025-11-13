<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Некорректный запрос');
}
$stmt = $pdo->prepare('DELETE FROM site_images WHERE id = :id AND asset_key = :key');
$stmt->execute([
    'id' => $id,
    'key' => 'production_gallery',
]);
header('Location: site_media.php');
exit;
