<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$assetKey = $_POST['asset_key'] ?? '';
if ($assetKey !== 'hero_banner') {
    die('Недопустимый ключ.');
}
$image = upload_single_image($_FILES['image'] ?? null);
if (!$image) {
    $_SESSION['error'] = 'Не удалось загрузить файл.';
    header('Location: site_media.php');
    exit;
}
$altText = trim($_POST['alt_text'] ?? '');
$pdo->beginTransaction();
try {
    $pdo->prepare('DELETE FROM site_images WHERE asset_key = :key')->execute(['key' => $assetKey]);
    $stmt = $pdo->prepare('INSERT INTO site_images (asset_key, image_data, alt_text, sort_order) VALUES (:key, :image, :alt, 0)');
    $stmt->execute([
        'key' => $assetKey,
        'image' => $image,
        'alt' => $altText ?: null,
    ]);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: site_media.php');
exit;
