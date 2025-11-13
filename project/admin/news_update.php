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
$imageName = null;
$oldStmt = $pdo->prepare('SELECT image FROM news WHERE id = :id');
$oldStmt->execute(['id' => $id]);
$old = $oldStmt->fetch();
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $imageName = uniqid('news_', true) . '.' . $ext;
    $targetDir = __DIR__ . '/../public_html/uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName);
    if ($old && !empty($old['image'])) {
        $file = __DIR__ . '/../public_html/uploads/' . $old['image'];
        if (is_file($file)) {
            unlink($file);
        }
    }
}
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
if ($imageName) {
    $sql .= ', image=:image';
    $params['image'] = $imageName;
}
$sql .= ' WHERE id=:id';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
header('Location: news_list.php');
exit;
