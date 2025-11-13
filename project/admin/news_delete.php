<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare('DELETE FROM news WHERE id = :id')->execute(['id' => $id]);
header('Location: news_list.php');
exit;
