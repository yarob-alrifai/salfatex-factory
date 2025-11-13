<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_POST['id'] ?? 0);
$category = sanitize_category($_POST['category'] ?? '');
if (!$category) {
    die('Invalid category');
}
$title = trim($_POST['group_title'] ?? '');
$leftDescription = trim($_POST['left_description'] ?? '');
$seoText = trim($_POST['seo_text'] ?? '');
$columns = $_POST['columns'] ?? [];
$rows = $_POST['rows'] ?? [];
$cells = $_POST['cells'] ?? [];
$pdo->beginTransaction();
try {
    $updateGroup = $pdo->prepare('UPDATE product_groups SET category=:category, group_title=:group_title, left_description=:left_description, seo_text=:seo_text WHERE id=:id');
    $updateGroup->execute([
        'category' => $category,
        'group_title' => $title,
        'left_description' => $leftDescription,
        'seo_text' => $seoText,
        'id' => $id,
    ]);
    $rowIds = $pdo->prepare('SELECT id FROM product_group_rows WHERE group_id = :id');
    $rowIds->execute(['id' => $id]);
    $ids = $rowIds->fetchAll(PDO::FETCH_COLUMN);
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("DELETE FROM product_group_cells WHERE row_id IN ($in)")->execute($ids);
    }
    $pdo->prepare('DELETE FROM product_group_rows WHERE group_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM product_group_columns WHERE group_id = :id')->execute(['id' => $id]);
    $columnIds = [];
    foreach ($columns as $index => $columnName) {
        $columnName = trim($columnName);
        if ($columnName === '') {
            continue;
        }
        $colStmt = $pdo->prepare('INSERT INTO product_group_columns (group_id, column_name, order_index) VALUES (:group_id, :column_name, :order_index)');
        $colStmt->execute([
            'group_id' => $id,
            'column_name' => $columnName,
            'order_index' => $index,
        ]);
        $columnIds[$index] = $pdo->lastInsertId();
    }
    foreach ($rows as $order => $rowIdentifier) {
        $rowStmt = $pdo->prepare('INSERT INTO product_group_rows (group_id, row_index) VALUES (:group_id, :row_index)');
        $rowStmt->execute([
            'group_id' => $id,
            'row_index' => $order,
        ]);
        $rowId = $pdo->lastInsertId();
        foreach ($columnIds as $colIndex => $columnId) {
            $value = $cells[$rowIdentifier][$colIndex] ?? '';
            $cellStmt = $pdo->prepare('INSERT INTO product_group_cells (row_id, column_id, value) VALUES (:row_id, :column_id, :value)');
            $cellStmt->execute([
                'row_id' => $rowId,
                'column_id' => $columnId,
                'value' => $value,
            ]);
        }
    }
    if (!empty($_FILES['images']['name'][0])) {
        $uploaded = upload_images($_FILES['images'], __DIR__ . '/../public_html/uploads/groups');
        foreach ($uploaded as $fileName) {
            $imgStmt = $pdo->prepare('INSERT INTO product_group_images (group_id, image_path) VALUES (:group_id, :image_path)');
            $imgStmt->execute([
                'group_id' => $id,
                'image_path' => $fileName,
            ]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: groups_list.php');
exit;
