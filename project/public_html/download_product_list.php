<?php
require_once __DIR__ . '/../inc/helpers.php';

$contact = get_contact_info($pdo);
if (!$contact || empty($contact['product_list_file'])) {
    http_response_code(404);
    exit('لم يتم العثور على ملف قائمة المنتجات.');
}

$fileName = $contact['product_list_file_name'] ?: 'product_list.xls';
$contentType = $contact['product_list_file_type'] ?: 'application/octet-stream';
$fileData = $contact['product_list_file'];

header('Content-Type: ' . $contentType);
$encodedFileName = rawurlencode($fileName);
$fallbackFileName = str_replace('"', '', $fileName);
header("Content-Disposition: attachment; filename=\"{$fallbackFileName}\"; filename*=UTF-8''{$encodedFileName}");
header('Content-Length: ' . strlen($fileData));
echo $fileData;
exit;
