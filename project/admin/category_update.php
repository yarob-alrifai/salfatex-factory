<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
if ($id <= 0 || $name === '' || $slug === '') {
    die('Invalid data');
}
$slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $slug), '-'));
if ($slug === '') {
    die('Slug is invalid');
}
$categoryStmt = $pdo->prepare('SELECT hero_image FROM product_categories WHERE id = :id');
$categoryStmt->execute(['id' => $id]);
$current = $categoryStmt->fetch();
if (!$current) {
    die('Category not found');
}
$existsStmt = $pdo->prepare('SELECT COUNT(*) FROM product_categories WHERE slug = :slug AND id != :id');
$existsStmt->execute(['slug' => $slug, 'id' => $id]);
if ($existsStmt->fetchColumn() > 0) {
    die('Slug already exists');
}
$newHero = upload_single_image($_FILES['hero_image'] ?? null);
$heroImage = $current['hero_image'];
if ($newHero) {
    $heroImage = $newHero;
}
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('UPDATE product_categories SET slug=:slug, name=:name, description=:description, hero_image=:hero_image WHERE id=:id');
    $stmt->execute([
        'slug' => $slug,
        'name' => $name,
        'description' => $description,
        'hero_image' => $heroImage,
        'id' => $id,
    ]);
    if (!empty($_FILES['gallery']['name'][0])) {
        $uploaded = upload_images($_FILES['gallery']);
        foreach ($uploaded as $fileName) {
            $pdo->prepare('INSERT INTO product_category_images (category_id, image_path) VALUES (:category_id, :image_path)')->execute([
                'category_id' => $id,
                'image_path' => $fileName,
            ]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: category_edit.php?id=' . $id);
exit;
