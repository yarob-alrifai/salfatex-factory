<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT hero_image FROM product_categories WHERE id = :id');
$stmt->execute(['id' => $id]);
$category = $stmt->fetch();
if ($category) {
    if (!empty($category['hero_image'])) {
        $file = __DIR__ . '/../public_html/uploads/categories/' . $category['hero_image'];
        if (is_file($file)) {
            unlink($file);
        }
    }
    $groupStmt = $pdo->prepare('SELECT id, main_image FROM product_groups WHERE category_id = :id');
    $groupStmt->execute(['id' => $id]);
    foreach ($groupStmt as $group) {
        if (!empty($group['main_image'])) {
            $file = __DIR__ . '/../public_html/uploads/groups/' . $group['main_image'];
            if (is_file($file)) {
                unlink($file);
            }
        }
        $imageStmt = $pdo->prepare('SELECT image_path FROM product_group_images WHERE group_id = :group_id');
        $imageStmt->execute(['group_id' => $group['id']]);
        foreach ($imageStmt as $image) {
            $file = __DIR__ . '/../public_html/uploads/groups/' . $image['image_path'];
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    $galleryStmt = $pdo->prepare('SELECT image_path FROM product_category_images WHERE category_id = :id');
    $galleryStmt->execute(['id' => $id]);
    foreach ($galleryStmt as $image) {
        $file = __DIR__ . '/../public_html/uploads/categories/' . $image['image_path'];
        if (is_file($file)) {
            unlink($file);
        }
    }
    $pdo->prepare('DELETE FROM product_category_images WHERE category_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM product_categories WHERE id = :id')->execute(['id' => $id]);
}
header('Location: categories_list.php');
exit;
