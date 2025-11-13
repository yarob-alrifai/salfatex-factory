<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$categoryId = (int)($_POST['category_id'] ?? 0);
$category = $categoryId ? get_category_by_id($categoryId) : null;
if (!$category) {
    die('Invalid category');
}
$title = trim($_POST['group_title'] ?? '');
$leftDescription = trim($_POST['left_description'] ?? '');
$seoText = trim($_POST['seo_text'] ?? '');
$columns = $_POST['columns'] ?? [];
$rows = $_POST['rows'] ?? [];
$cells = $_POST['cells'] ?? [];
$mainImage = upload_single_image($_FILES['main_image'] ?? null);
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO product_groups (category_id, group_title, main_image, left_description, seo_text, created_at) VALUES (:category_id, :group_title, :main_image, :left_description, :seo_text, NOW())');
    $stmt->execute([
        'category_id' => $category['id'],
        'group_title' => $title,
        'main_image' => $mainImage,
        'left_description' => $leftDescription,
        'seo_text' => $seoText,
    ]);
    $groupId = $pdo->lastInsertId();
    $columnIds = [];
    foreach ($columns as $index => $columnName) {
        $columnName = trim($columnName);
        if ($columnName === '') {
            continue;
        }
        $colStmt = $pdo->prepare('INSERT INTO product_group_columns (group_id, column_name, order_index) VALUES (:group_id, :column_name, :order_index)');
        $colStmt->execute([
            'group_id' => $groupId,
            'column_name' => $columnName,
            'order_index' => $index,
        ]);
        $columnIds[$index] = $pdo->lastInsertId();
    }
    foreach ($rows as $order => $rowIdentifier) {
        $rowStmt = $pdo->prepare('INSERT INTO product_group_rows (group_id, row_index) VALUES (:group_id, :row_index)');
        $rowStmt->execute([
            'group_id' => $groupId,
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
        $uploaded = upload_images($_FILES['images']);
        foreach ($uploaded as $fileName) {
            $imgStmt = $pdo->prepare('INSERT INTO product_group_images (group_id, image_path) VALUES (:group_id, :image_path)');
            $imgStmt->execute([
                'group_id' => $groupId,
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
