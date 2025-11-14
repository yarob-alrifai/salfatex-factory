<?php
require_once __DIR__ . '/../inc/helpers.php';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT slug FROM product_groups WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $slug = $stmt->fetchColumn();
    if ($slug) {
        header('Location: group.php?slug=' . urlencode($slug), true, 301);
        exit;
    }
}
http_response_code(404);
 echo 'Группа не найдена';
