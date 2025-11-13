<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$categoryId = (int)($_GET['category_id'] ?? 0);
$stmt = $pdo->prepare('SELECT image_path FROM product_category_images WHERE id = :id');
$stmt->execute(['id' => $id]);
$image = $stmt->fetch();
if ($image) {
    $file = __DIR__ . '/../public_html/uploads/categories/' . $image['image_path'];
    if (is_file($file)) {
        unlink($file);
    }
    $pdo->prepare('DELETE FROM product_category_images WHERE id = :id')->execute(['id' => $id]);
}
header('Location: category_edit.php?id=' . $categoryId);
exit;
