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
    'phone_secondary_alt' => trim($_POST['phone_secondary_alt'] ?? ''),
    'sales_rep_name' => trim($_POST['sales_rep_name'] ?? ''),
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
    'product_list_label' => trim($_POST['product_list_label'] ?? ''),
    'product_list_file' => null,
    'product_list_file_name' => null,
    'product_list_file_type' => null,
];
$data['product_list_label'] = $data['product_list_label'] !== '' ? $data['product_list_label'] : 'قائمة المنتجات';
$navbarIconUpload = upload_single_image($_FILES['navbar_icon'] ?? null);
$removeNavbarIcon = !empty($_POST['remove_navbar_icon']);
$stmt = $pdo->query('SELECT id, navbar_icon, product_list_file, product_list_file_name, product_list_file_type FROM contact_info LIMIT 1');
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
$existingFile = $existing['product_list_file'] ?? null;
$removeProductListFile = !empty($_POST['remove_product_list_file']);

$productListUpload = $_FILES['product_list_file'] ?? null;
if ($productListUpload && ($productListUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $allowedExtensions = ['xls', 'xlsx'];
    $extension = strtolower(pathinfo($productListUpload['name'] ?? '', PATHINFO_EXTENSION));
    $size = (int)($productListUpload['size'] ?? 0);
    if (in_array($extension, $allowedExtensions, true) && $size > 0 && $size <= 10 * 1024 * 1024) {
        $fileContents = file_get_contents($productListUpload['tmp_name']);
        if ($fileContents !== false) {
            $data['product_list_file'] = $fileContents;
            $data['product_list_file_name'] = basename($productListUpload['name'] ?? 'product_list.' . $extension);
            $data['product_list_file_type'] = !empty($productListUpload['type']) ? $productListUpload['type'] : 'application/octet-stream';
        }
    }
} elseif ($removeProductListFile) {
    $data['product_list_file'] = null;
    $data['product_list_file_name'] = null;
    $data['product_list_file_type'] = null;
} else {
    $data['product_list_file'] = $existingFile;
    $data['product_list_file_name'] = $existing['product_list_file_name'] ?? null;
    $data['product_list_file_type'] = $existing['product_list_file_type'] ?? null;
}
if ($navbarIconUpload !== null) {
    $data['navbar_icon'] = $navbarIconUpload;
} elseif ($removeNavbarIcon) {
    $data['navbar_icon'] = null;
} else {
    $data['navbar_icon'] = $existing['navbar_icon'] ?? null;
}
if ($existing) {
    $sql = 'UPDATE contact_info SET slug=:slug, h1=:h1, navbar_company_name=:navbar_company_name, navbar_icon=:navbar_icon, phone_main=:phone_main, phone_secondary=:phone_secondary, phone_secondary_alt=:phone_secondary_alt, sales_rep_name=:sales_rep_name, email=:email, whatsapp_link=:whatsapp_link, telegram_link=:telegram_link, address=:address, map_embed=:map_embed, seo_text=:seo_text, meta_title=:meta_title, meta_description=:meta_description, meta_keywords=:meta_keywords, canonical_url=:canonical_url, product_list_label=:product_list_label, product_list_file=:product_list_file, product_list_file_name=:product_list_file_name, product_list_file_type=:product_list_file_type WHERE id=:id';
    $data['id'] = $existing['id'];
    $update = $pdo->prepare($sql);
    foreach ($data as $key => $value) {
        $param = $key === 'product_list_file' ? PDO::PARAM_LOB : PDO::PARAM_STR;
        $update->bindValue(':' . $key, $value, $param);
    }
    $update->execute();
} else {
    $sql = 'INSERT INTO contact_info (slug, h1, navbar_company_name, navbar_icon, phone_main, phone_secondary, phone_secondary_alt, sales_rep_name, email, whatsapp_link, telegram_link, address, map_embed, seo_text, meta_title, meta_description, meta_keywords, canonical_url, product_list_label, product_list_file, product_list_file_name, product_list_file_type) VALUES (:slug, :h1, :navbar_company_name, :navbar_icon, :phone_main, :phone_secondary, :phone_secondary_alt, :sales_rep_name, :email, :whatsapp_link, :telegram_link, :address, :map_embed, :seo_text, :meta_title, :meta_description, :meta_keywords, :canonical_url, :product_list_label, :product_list_file, :product_list_file_name, :product_list_file_type)';
    $insert = $pdo->prepare($sql);
    foreach ($data as $key => $value) {
        $param = $key === 'product_list_file' ? PDO::PARAM_LOB : PDO::PARAM_STR;
        $insert->bindValue(':' . $key, $value, $param);
    }
    $insert->execute();
}
header('Location: contact_edit.php');
exit;
