<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('news_form');
$id = (int)($_POST['id'] ?? 0);
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
if ($id <= 0) {
    die('Invalid ID');
}
$slug = sanitize_slug($slugInput);
$exists = $pdo->prepare('SELECT COUNT(*) FROM news WHERE slug = :slug AND id != :id');
$exists->execute(['slug' => $slug, 'id' => $id]);
if ($exists->fetchColumn() > 0) {
    die('Slug already exists');
}
$imageData = upload_single_image($_FILES['image'] ?? null);
$ogImage = upload_single_image($_FILES['og_image'] ?? null);
$sql = 'UPDATE news SET slug=:slug, title=:title, h1=:h1, short_text=:short_text, full_text=:full_text, image_alt=:image_alt, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords, og_title=:og_title, og_description=:og_description, canonical_url=:canonical_url';
$params = [
    'slug' => $slug,
    'title' => $title,
    'h1' => $h1,
    'short_text' => $short,
    'full_text' => $full,
    'image_alt' => $imageAlt,
    'meta_title' => $metaTitle,
    'meta_description' => $metaDescription,
    'meta_keywords' => $metaKeywords,
    'og_title' => $ogTitle,
    'og_description' => $ogDescription,
    'canonical_url' => $canonicalUrl,
    'id' => $id,
];
if ($imageData) {
    $sql .= ', image=:image';
    $params['image'] = $imageData;
}
if (!empty($_POST['remove_og_image'])) {
    $sql .= ', og_image=NULL';
} elseif ($ogImage) {
    $sql .= ', og_image=:og_image';
    $params['og_image'] = $ogImage;
}
$sql .= ' WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
header('Location: news_list.php');
exit;
