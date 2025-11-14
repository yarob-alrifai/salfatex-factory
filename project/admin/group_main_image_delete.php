<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT main_image FROM product_groups WHERE id = :id');
$stmt->execute(['id' => $id]);
$image = $stmt->fetchColumn();
if ($image) {
    $pdo->prepare('UPDATE product_groups SET main_image = NULL, main_image_alt = NULL WHERE id = :id')->execute(['id' => $id]);
}
header('Location: group_edit.php?id=' . $id);
exit;
