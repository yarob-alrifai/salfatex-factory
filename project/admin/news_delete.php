<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT image FROM news WHERE id = :id');
$stmt->execute(['id' => $id]);
$news = $stmt->fetch();
if ($news && !empty($news['image'])) {
    $file = __DIR__ . '/../public_html/uploads/' . $news['image'];
    if (is_file($file)) {
        unlink($file);
    }
}
$pdo->prepare('DELETE FROM news WHERE id = :id')->execute(['id' => $id]);
header('Location: news_list.php');
exit;
