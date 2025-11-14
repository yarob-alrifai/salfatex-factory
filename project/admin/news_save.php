<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('news_form');
$title = trim($_POST['title'] ?? '');
$slugInput = trim($_POST['slug'] ?? $title);
$h1 = trim($_POST['h1'] ?? '');
$short = trim($_POST['short_text'] ?? '');
$full = $_POST['full_text'] ?? '';
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$imageAlt = trim($_POST['image_alt'] ?? '');
$ogTitle = trim($_POST['og_title'] ?? '');
$ogDescription = trim($_POST['og_description'] ?? '');
$canonicalUrl = trim($_POST['canonical_url'] ?? '');
if ($title === '' || $slugInput === '' || $short === '' || $full === '') {
    die('Заполните все обязательные поля');
}
$slug = sanitize_slug($slugInput);
$exists = $pdo->prepare('SELECT COUNT(*) FROM news WHERE slug = :slug');
$exists->execute(['slug' => $slug]);
if ($exists->fetchColumn() > 0) {
    die('Slug already exists');
}
$imageData = upload_single_image($_FILES['image'] ?? null);
$ogImage = upload_single_image($_FILES['og_image'] ?? null);
$stmt = $pdo->prepare('INSERT INTO news (slug, title, h1, short_text, full_text, image, image_alt, meta_title, meta_description, meta_keywords, og_title, og_description, og_image, canonical_url, created_at) VALUES (:slug, :title, :h1, :short_text, :full_text, :image, :image_alt, :meta_title, :meta_description, :meta_keywords, :og_title, :og_description, :og_image, :canonical_url, NOW())');
$stmt->execute([
    'slug' => $slug,
    'title' => $title,
    'h1' => $h1,
    'short_text' => $short,
    'full_text' => $full,
    'image' => $imageData,
    'image_alt' => $imageAlt,
    'meta_title' => $metaTitle,
    'meta_description' => $metaDescription,
    'meta_keywords' => $metaKeywords,
    'og_title' => $ogTitle,
    'og_description' => $ogDescription,
    'og_image' => $ogImage,
    'canonical_url' => $canonicalUrl,
]);
header('Location: news_list.php');
exit;
