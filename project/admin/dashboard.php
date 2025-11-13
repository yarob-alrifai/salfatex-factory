<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$newsCount = $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
$groupCount = $pdo->query('SELECT COUNT(*) FROM product_groups')->fetchColumn();
$rowCount = $pdo->query('SELECT COUNT(*) FROM product_group_rows')->fetchColumn();
$messageCount = $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
admin_header('Dashboard');
?>
<div class="stats-grid">
    <div>News: <?php echo (int)$newsCount; ?></div>
    <div>Groups: <?php echo (int)$groupCount; ?></div>
    <div>Rows: <?php echo (int)$rowCount; ?></div>
    <div>Messages: <?php echo (int)$messageCount; ?></div>
</div>
<?php admin_footer(); ?>
