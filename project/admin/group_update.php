<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_POST['id'] ?? 0);
$categoryId = (int)($_POST['category_id'] ?? 0);
$category = $categoryId ? get_category_by_id($categoryId) : null;
if (!$category) {
    die('Invalid category');
}
$currentGroupStmt = $pdo->prepare('SELECT main_image FROM product_groups WHERE id = :id');
$currentGroupStmt->execute(['id' => $id]);
$currentGroup = $currentGroupStmt->fetch();
if (!$currentGroup) {
    die('Group not found');
}
$title = trim($_POST['group_title'] ?? '');
$leftDescription = trim($_POST['left_description'] ?? '');
$seoText = trim($_POST['seo_text'] ?? '');
$columns = $_POST['columns'] ?? [];
$rows = $_POST['rows'] ?? [];
$cells = $_POST['cells'] ?? [];
$newMainImage = upload_single_image($_FILES['main_image'] ?? null, __DIR__ . '/../public_html/uploads/groups');
$mainImage = $currentGroup['main_image'];
if ($newMainImage) {
    if ($mainImage) {
        $file = __DIR__ . '/../public_html/uploads/groups/' . $mainImage;
        if (is_file($file)) {
            unlink($file);
        }
    }
    $mainImage = $newMainImage;
}
$pdo->beginTransaction();
try {
    $updateGroup = $pdo->prepare('UPDATE product_groups SET category_id=:category_id, group_title=:group_title, main_image=:main_image, left_description=:left_description, seo_text=:seo_text WHERE id=:id');
    $updateGroup->execute([
        'category_id' => $category['id'],
        'group_title' => $title,
        'main_image' => $mainImage,
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
