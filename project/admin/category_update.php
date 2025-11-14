<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('category_form');
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$slugInput = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$seoText = trim($_POST['seo_text'] ?? '');
$h1 = trim($_POST['h1'] ?? '');
$heroAlt = trim($_POST['hero_image_alt'] ?? '');
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$ogTitle = trim($_POST['og_title'] ?? '');
$ogDescription = trim($_POST['og_description'] ?? '');
$canonicalUrl = trim($_POST['canonical_url'] ?? '');
$galleryDefaultAlt = trim($_POST['gallery_default_alt'] ?? '') ?: $name;
if ($id <= 0 || $name === '' || $slugInput === '') {
    die('Invalid data');
}
$slug = sanitize_slug($slugInput);
$categoryStmt = $pdo->prepare('SELECT hero_image, og_image FROM product_categories WHERE id = :id');
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
$heroImage = $newHero ?: $current['hero_image'];
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
    $stmt = $pdo->prepare('UPDATE product_categories SET slug=:slug, name=:name, h1=:h1, description=:description, seo_text=:seo_text, hero_image=:hero_image, hero_image_alt=:hero_image_alt, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords, og_title=:og_title, og_description=:og_description, og_image=:og_image, canonical_url=:canonical_url WHERE id=:id');
    $stmt->execute([
        'slug' => $slug,
        'name' => $name,
        'h1' => $h1,
        'description' => $description,
        'seo_text' => $seoText,
        'hero_image' => $heroImage,
        'hero_image_alt' => $heroAlt,
        'meta_title' => $metaTitle,
        'meta_description' => $metaDescription,
        'meta_keywords' => $metaKeywords,
        'og_title' => $ogTitle,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'canonical_url' => $canonicalUrl,
        'id' => $id,
    ]);
    if (!empty($_FILES['gallery']['name'][0])) {
        $uploaded = upload_images($_FILES['gallery']);
        foreach ($uploaded as $fileName) {
            $pdo->prepare('INSERT INTO product_category_images (category_id, image_path, alt_text) VALUES (:category_id, :image_path, :alt_text)')->execute([
                'category_id' => $id,
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
        $pdo->prepare('UPDATE product_category_images SET alt_text = :alt WHERE id = :id AND category_id = :category_id')->execute([
            'alt' => trim($altText),
            'id' => $imageId,
            'category_id' => $id,
        ]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
header('Location: category_edit.php?id=' . $id);
exit;
