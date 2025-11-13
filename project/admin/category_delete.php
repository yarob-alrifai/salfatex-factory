<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT id FROM product_categories WHERE id = :id');
$stmt->execute(['id' => $id]);
$category = $stmt->fetch();
if ($category) {
    $pdo->prepare('DELETE FROM product_category_images WHERE category_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM product_categories WHERE id = :id')->execute(['id' => $id]);
}
header('Location: categories_list.php');
exit;
