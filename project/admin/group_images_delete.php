<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$groupId = (int)($_GET['group_id'] ?? 0);
$stmt = $pdo->prepare('SELECT image_path FROM product_group_images WHERE id = :id');
$stmt->execute(['id' => $id]);
$image = $stmt->fetch();
if ($image) {
    $pdo->prepare('DELETE FROM product_group_images WHERE id = :id')->execute(['id' => $id]);
}
header('Location: group_edit.php?id=' . $groupId);
exit;
