<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();
require_csrf_token('contact_form');
$data = [
    'slug' => sanitize_slug($_POST['slug'] ?? 'contact'),
    'h1' => trim($_POST['h1'] ?? ''),
    'navbar_company_name' => trim($_POST['navbar_company_name'] ?? ''),
    'phone_main' => trim($_POST['phone_main'] ?? ''),
    'phone_secondary' => trim($_POST['phone_secondary'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'whatsapp_link' => trim($_POST['whatsapp_link'] ?? ''),
    'telegram_link' => trim($_POST['telegram_link'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'map_embed' => trim($_POST['map_embed'] ?? ''),
    'seo_text' => trim($_POST['seo_text'] ?? ''),
    'meta_title' => trim($_POST['meta_title'] ?? ''),
    'meta_description' => trim($_POST['meta_description'] ?? ''),
    'meta_keywords' => trim($_POST['meta_keywords'] ?? ''),
    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
];
$navbarIconUpload = upload_single_image($_FILES['navbar_icon'] ?? null);
$removeNavbarIcon = !empty($_POST['remove_navbar_icon']);
$stmt = $pdo->query('SELECT id, navbar_icon FROM contact_info LIMIT 1');
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($navbarIconUpload !== null) {
    $data['navbar_icon'] = $navbarIconUpload;
} elseif ($removeNavbarIcon) {
    $data['navbar_icon'] = null;
} else {
    $data['navbar_icon'] = $existing['navbar_icon'] ?? null;
}
if ($existing) {
    $sql = 'UPDATE contact_info SET slug=:slug, h1=:h1, navbar_company_name=:navbar_company_name, navbar_icon=:navbar_icon, phone_main=:phone_main, phone_secondary=:phone_secondary, email=:email, whatsapp_link=:whatsapp_link, telegram_link=:telegram_link, address=:address, map_embed=:map_embed, seo_text=:seo_text, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords, canonical_url=:canonical_url WHERE id=:id';
    $data['id'] = $existing['id'];
    $update = $pdo->prepare($sql);
    $update->execute($data);
} else {
    $sql = 'INSERT INTO contact_info (slug, h1, navbar_company_name, navbar_icon, phone_main, phone_secondary, email, whatsapp_link, telegram_link, address, map_embed, seo_text, meta_title, meta_description, meta_keywords, canonical_url) VALUES (:slug, :h1, :navbar_company_name, :navbar_icon, :phone_main, :phone_secondary, :email, :whatsapp_link, :telegram_link, :address, :map_embed, :seo_text, :meta_title, :meta_description, :meta_keywords, :canonical_url)';
    $insert = $pdo->prepare($sql);
    $insert->execute($data);
}
header('Location: contact_edit.php');
exit;
