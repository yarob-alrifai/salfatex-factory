<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$rowIdsStmt = $pdo->prepare('SELECT id FROM product_group_rows WHERE group_id = :id');
$rowIdsStmt->execute(['id' => $id]);
$rowIds = $rowIdsStmt->fetchAll(PDO::FETCH_COLUMN);
if ($rowIds) {
    $in = implode(',', array_fill(0, count($rowIds), '?'));
    $pdo->prepare("DELETE FROM product_group_cells WHERE row_id IN ($in)")->execute($rowIds);
}
$pdo->prepare('DELETE FROM product_group_images WHERE group_id = :id')->execute(['id' => $id]);
$pdo->prepare('DELETE FROM product_group_rows WHERE group_id = :id')->execute(['id' => $id]);
$pdo->prepare('DELETE FROM product_group_columns WHERE group_id = :id')->execute(['id' => $id]);
$pdo->prepare('DELETE FROM product_groups WHERE id = :id')->execute(['id' => $id]);
header('Location: groups_list.php');
exit;
