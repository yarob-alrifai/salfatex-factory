<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$images = upload_images($_FILES['gallery_images'] ?? []);
if (!$images) {
    $_SESSION['error'] = 'Не выбраны изображения.';
    header('Location: site_media.php');
    exit;
}
$altText = trim($_POST['gallery_alt'] ?? '');
$stmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM site_images WHERE asset_key = :key');
$stmt->execute(['key' => 'production_gallery']);
$sortOrder = (int)$stmt->fetchColumn();
$pdo->beginTransaction();
try {
    foreach ($images as $index => $image) {
        $pdo->prepare('INSERT INTO site_images (asset_key, image_data, alt_text, sort_order) VALUES (:key, :image, :alt, :sort)')->execute([
            'key' => 'production_gallery',
            'image' => $image,
            'alt' => $altText ?: null,
            'sort' => $sortOrder + $index + 1,
        ]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: site_media.php');
exit;
