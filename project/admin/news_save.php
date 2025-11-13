<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$title = trim($_POST['title'] ?? '');
$short = trim($_POST['short_text'] ?? '');
$full = $_POST['full_text'] ?? '';
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$imageName = '';
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('news_', true) . '.' . $ext;
    $targetDir = __DIR__ . '/../public_html/uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName);
}
$stmt = $pdo->prepare('INSERT INTO news (title, short_text, full_text, image, meta_title, meta_description, meta_keywords, created_at) VALUES (:title, :short_text, :full_text, :image, :meta_title, :meta_description, :meta_keywords, NOW())');
$stmt->execute([
    'title' => $title,
    'short_text' => $short,
    'full_text' => $full,
    'image' => $imageName,
    'meta_title' => $metaTitle,
    'meta_description' => $metaDescription,
    'meta_keywords' => $metaKeywords,
]);
header('Location: news_list.php');
exit;
