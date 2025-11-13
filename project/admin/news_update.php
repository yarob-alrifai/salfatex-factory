<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$short = trim($_POST['short_text'] ?? '');
$full = $_POST['full_text'] ?? '';
$metaTitle = trim($_POST['meta_title'] ?? '');
$metaDescription = trim($_POST['meta_description'] ?? '');
$metaKeywords = trim($_POST['meta_keywords'] ?? '');
$imageData = upload_single_image($_FILES['image'] ?? null);
$sql = 'UPDATE news SET title=:title, short_text=:short_text, full_text=:full_text, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords';
$params = [
    'title' => $title,
    'short_text' => $short,
    'full_text' => $full,
    'meta_title' => $metaTitle,
    'meta_description' => $metaDescription,
    'meta_keywords' => $metaKeywords,
    'id' => $id,
];
if ($imageData) {
    $sql .= ', image=:image';
    $params['image'] = $imageData;
}
$sql .= ' WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
header('Location: news_list.php');
exit;
