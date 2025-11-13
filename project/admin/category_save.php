<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($name === '' || $slug === '') {
    die('Name and slug are required');
}
$slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $slug), '-'));
if ($slug === '') {
    die('Slug is invalid');
}
$existsStmt = $pdo->prepare('SELECT COUNT(*) FROM product_categories WHERE slug = :slug');
$existsStmt->execute(['slug' => $slug]);
if ($existsStmt->fetchColumn() > 0) {
    die('Slug already exists');
}
$heroImage = upload_single_image($_FILES['hero_image'] ?? null, __DIR__ . '/../public_html/uploads/categories');
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO product_categories (slug, name, description, hero_image, created_at) VALUES (:slug, :name, :description, :hero_image, NOW())');
    $stmt->execute([
        'slug' => $slug,
        'name' => $name,
        'description' => $description,
        'hero_image' => $heroImage,
    ]);
    $categoryId = $pdo->lastInsertId();
    if (!empty($_FILES['gallery']['name'][0])) {
        $uploaded = upload_images($_FILES['gallery'], __DIR__ . '/../public_html/uploads/categories');
        foreach ($uploaded as $fileName) {
            $pdo->prepare('INSERT INTO product_category_images (category_id, image_path) VALUES (:category_id, :image_path)')->execute([
                'category_id' => $categoryId,
                'image_path' => $fileName,
            ]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: categories_list.php');
exit;
