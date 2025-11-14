<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('group_form');
$id = (int)($_POST['id'] ?? 0);
$categoryId = (int)($_POST['category_id'] ?? 0);
$category = $categoryId ? get_category_by_id($categoryId) : null;
if ($id <= 0 || !$category) {
    die('Invalid data');
}
$title = trim($_POST['group_title'] ?? '');
$slugInput = trim($_POST['slug'] ?? $title);
$h1 = trim($_POST['h1'] ?? '');
$leftDescription = trim($_POST['left_description'] ?? '');
$seoText = trim($_POST['seo_text'] ?? '');
$mainImageAlt = trim($_POST['main_image_alt'] ?? '');
$galleryDefaultAlt = trim($_POST['gallery_default_alt'] ?? '') ?: $title;
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$ogTitle = trim($_POST['og_title'] ?? '');
$ogDescription = trim($_POST['og_description'] ?? '');
$canonicalUrl = trim($_POST['canonical_url'] ?? '');
$columns = $_POST['columns'] ?? [];
$rows = $_POST['rows'] ?? [];
$cells = $_POST['cells'] ?? [];
if ($title === '' || $slugInput === '' || $leftDescription === '') {
    die('Missing fields');
}
$slug = sanitize_slug($slugInput);
$currentStmt = $pdo->prepare('SELECT * FROM product_groups WHERE id = :id');
$currentStmt->execute(['id' => $id]);
$current = $currentStmt->fetch();
if (!$current) {
    die('Group not found');
}
$existsStmt = $pdo->prepare('SELECT COUNT(*) FROM product_groups WHERE slug = :slug AND id != :id');
$existsStmt->execute(['slug' => $slug, 'id' => $id]);
if ($existsStmt->fetchColumn() > 0) {
    die('Slug already exists');
}
$newMainImage = upload_single_image($_FILES['main_image'] ?? null);
$mainImage = $newMainImage ?: $current['main_image'];
$newOg = upload_single_image($_FILES['og_image'] ?? null);
$ogImage = $current['og_image'];
if (!empty($_POST['remove_og_image'])) {
    $ogImage = null;
}
if ($newOg) {
    $ogImage = $newOg;
}
$galleryAltUpdates = $_POST['gallery_alt'] ?? [];
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('UPDATE product_groups SET category_id=:category_id, slug=:slug, group_title=:group_title, h1=:h1, main_image=:main_image, main_image_alt=:main_image_alt, left_description=:left_description, seo_text=:seo_text, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords, og_title=:og_title, og_description=:og_description, og_image=:og_image, canonical_url=:canonical_url WHERE id=:id');
    $stmt->execute([
        'category_id' => $category['id'],
        'slug' => $slug,
        'group_title' => $title,
        'h1' => $h1,
        'main_image' => $mainImage,
        'main_image_alt' => $mainImageAlt,
        'left_description' => $leftDescription,
        'seo_text' => $seoText,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'meta_keywords' => $metaKeywords,
        'og_title' => $ogTitle,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'canonical_url' => $canonicalUrl,
        'id' => $id,
    ]);
    $pdo->prepare('DELETE FROM product_group_columns WHERE group_id = :id')->execute(['id' => $id]);
    $pdo->prepare('DELETE FROM product_group_rows WHERE group_id = :id')->execute(['id' => $id]);
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
            $value = trim($cells[$rowIdentifier][$colIndex] ?? '');
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
            $pdo->prepare('INSERT INTO product_group_images (group_id, image_path, alt_text) VALUES (:group_id, :image_path, :alt_text)')->execute([
                'group_id' => $id,
                'image_path' => $fileName,
                'alt_text' => $galleryDefaultAlt,
            ]);
        }
    }
    foreach ($galleryAltUpdates as $imageId => $altText) {
        $imageId = (int)$imageId;
        if ($imageId <= 0) {
            continue;
        }
        $pdo->prepare('UPDATE product_group_images SET alt_text = :alt WHERE id = :id AND group_id = :group_id')->execute([
            'alt' => trim($altText),
            'id' => $imageId,
            'group_id' => $id,
        ]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: group_edit.php?id=' . $id);
exit;
