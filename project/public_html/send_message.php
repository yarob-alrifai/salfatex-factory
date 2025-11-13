<?php
require_once __DIR__ . '/../inc/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}
$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
if (!$name || !$phone || !$email || !$message) {
    $_SESSION['flash'] = 'Пожалуйста, заполните все поля.';
    header('Location: contact.php');
    exit;
}
$stmt = $pdo->prepare('INSERT INTO messages (name, phone, email, message, created_at) VALUES (:name, :phone, :email, :message, NOW())');
$stmt->execute([
    'name' => $name,
    'phone' => $phone,
    'email' => $email,
    'message' => $message,
]);
$_SESSION['flash'] = 'Сообщение отправлено. Мы свяжемся с вами в ближайшее время.';
header('Location: contact.php');
exit;
