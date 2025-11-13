<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$groupId = (int)($_POST['group_id'] ?? 0);
$values = $_POST['values'] ?? [];
$rowIndexStmt = $pdo->prepare('SELECT IFNULL(MAX(row_index), 0) + 1 FROM product_group_rows WHERE group_id = :id');
$rowIndexStmt->execute(['id' => $groupId]);
$rowIndex = (int)$rowIndexStmt->fetchColumn();
$rowStmt = $pdo->prepare('INSERT INTO product_group_rows (group_id, row_index) VALUES (:group_id, :row_index)');
$rowStmt->execute([
    'group_id' => $groupId,
    'row_index' => $rowIndex,
]);
$rowId = $pdo->lastInsertId();
foreach ($values as $columnId => $value) {
    $cellStmt = $pdo->prepare('INSERT INTO product_group_cells (row_id, column_id, value) VALUES (:row_id, :column_id, :value)');
    $cellStmt->execute([
        'row_id' => $rowId,
        'column_id' => (int)$columnId,
        'value' => $value,
    ]);
}
header('Location: variants_list.php?group_id=' . $groupId);
exit;
