<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/../inc/auth.php';
admin_logout();
header('Location: index.php');
exit;
