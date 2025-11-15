<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT id FROM messages WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    if ($stmt->fetch()) {
        $deleteStmt = $pdo->prepare('DELETE FROM messages WHERE id = :id');
        $deleteStmt->execute(['id' => $id]);
    }
}

header('Location: messages_list.php');
exit;
