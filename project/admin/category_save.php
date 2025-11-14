<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('category_form');
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
if ($name === '' || $slugInput === '') {
    die('Name and slug are required');
}
$slug = sanitize_slug($slugInput);
$existsStmt = $pdo->prepare('SELECT COUNT(*) FROM product_categories WHERE slug = :slug');
$existsStmt->execute(['slug' => $slug]);
if ($existsStmt->fetchColumn() > 0) {
    die('Slug already exists');
}
$heroImage = upload_single_image($_FILES['hero_image'] ?? null);
$ogImage = upload_single_image($_FILES['og_image'] ?? null);
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO product_categories (slug, name, h1, description, seo_text, hero_image, hero_image_alt, meta_title, meta_description, meta_keywords, og_title, og_description, og_image, canonical_url, created_at) VALUES (:slug, :name, :h1, :description, :seo_text, :hero_image, :hero_image_alt, :meta_title, :meta_description, :meta_keywords, :og_title, :og_description, :og_image, :canonical_url, NOW())');
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
    ]);
    $categoryId = (int)$pdo->lastInsertId();
    if (!empty($_FILES['gallery']['name'][0])) {
        $uploaded = upload_images($_FILES['gallery']);
        foreach ($uploaded as $fileName) {
            $pdo->prepare('INSERT INTO product_category_images (category_id, image_path, alt_text) VALUES (:category_id, :image_path, :alt_text)')->execute([
                'category_id' => $categoryId,
                'image_path' => $fileName,
                'alt_text' => $galleryDefaultAlt,
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
