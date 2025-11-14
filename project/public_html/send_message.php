<?php
require_once __DIR__ . '/../inc/helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}
require_csrf_token('public_contact');
$name = trim(strip_tags($_POST['name'] ?? ''));
$phone = trim(strip_tags($_POST['phone'] ?? ''));
$emailRaw = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
if (!$name || !$phone || !$email || !$message) {
    $_SESSION['flash'] = 'Пожалуйста, заполните все поля корректно.';
    header('Location: contact.php');
    exit;
}
if (mb_strlen($message) > 2000) {
    $_SESSION['flash'] = 'Сообщение слишком длинное.';
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
