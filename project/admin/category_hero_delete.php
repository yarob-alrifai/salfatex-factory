<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT hero_image FROM product_categories WHERE id = :id');
$stmt->execute(['id' => $id]);
$image = $stmt->fetchColumn();
if ($image) {
    $pdo->prepare('UPDATE product_categories SET hero_image = NULL WHERE id = :id')->execute(['id' => $id]);
}
header('Location: category_edit.php?id=' . $id);
exit;
