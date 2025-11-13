<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$title = trim($_POST['title'] ?? '');
$short = trim($_POST['short_text'] ?? '');
$full = $_POST['full_text'] ?? '';
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$imageData = upload_single_image($_FILES['image'] ?? null);
$stmt = $pdo->prepare('INSERT INTO news (title, short_text, full_text, image, meta_title, meta_description, meta_keywords, created_at) VALUES (:title, :short_text, :full_text, :image, :meta_title, :meta_description, :meta_keywords, NOW())');
$stmt->execute([
    'title' => $title,
    'short_text' => $short,
    'full_text' => $full,
    'image' => $imageData,
    'meta_title' => $metaTitle,
    'meta_description' => $metaDescription,
    'meta_keywords' => $metaKeywords,
]);
header('Location: news_list.php');
exit;
