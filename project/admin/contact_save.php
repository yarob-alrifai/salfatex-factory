<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
$data = [
    'phone_main' => trim($_POST['phone_main'] ?? ''),
    'phone_secondary' => trim($_POST['phone_secondary'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'whatsapp_link' => trim($_POST['whatsapp_link'] ?? ''),
    'telegram_link' => trim($_POST['telegram_link'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'map_embed' => trim($_POST['map_embed'] ?? ''),
];
$stmt = $pdo->query('SELECT id FROM contact_info LIMIT 1');
$existing = $stmt->fetchColumn();
if ($existing) {
    $sql = 'UPDATE contact_info SET phone_main=:phone_main, phone_secondary=:phone_secondary, email=:email, whatsapp_link=:whatsapp_link, telegram_link=:telegram_link, address=:address, map_embed=:map_embed WHERE id=:id';
    $data['id'] = $existing;
    $update = $pdo->prepare($sql);
    $update->execute($data);
} else {
    $sql = 'INSERT INTO contact_info (phone_main, phone_secondary, email, whatsapp_link, telegram_link, address, map_embed) VALUES (:phone_main, :phone_secondary, :email, :whatsapp_link, :telegram_link, :address, :map_embed)';
    $insert = $pdo->prepare($sql);
    $insert->execute($data);
}
header('Location: contact_edit.php');
exit;
