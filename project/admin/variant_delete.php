<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$groupId = (int)($_GET['group_id'] ?? 0);
$pdo->prepare('DELETE FROM product_group_cells WHERE row_id = :row_id')->execute(['row_id' => $id]);
$pdo->prepare('DELETE FROM product_group_rows WHERE id = :id')->execute(['id' => $id]);
header('Location: variants_list.php?group_id=' . $groupId);
exit;
