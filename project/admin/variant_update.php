<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_POST['id'] ?? 0);
$groupId = (int)($_POST['group_id'] ?? 0);
$values = $_POST['values'] ?? [];
$pdo->prepare('DELETE FROM product_group_cells WHERE row_id = :row_id')->execute(['row_id' => $id]);
foreach ($values as $columnId => $value) {
    $stmt = $pdo->prepare('INSERT INTO product_group_cells (row_id, column_id, value) VALUES (:row_id, :column_id, :value)');
    $stmt->execute([
        'row_id' => $id,
        'column_id' => (int)$columnId,
        'value' => $value,
    ]);
}
header('Location: variants_list.php?group_id=' . $groupId);
exit;
