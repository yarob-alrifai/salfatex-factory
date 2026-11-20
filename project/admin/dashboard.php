<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
$newsCount = $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
$categoryCount = $pdo->query('SELECT COUNT(*) FROM product_categories')->fetchColumn();
$groupCount = $pdo->query('SELECT COUNT(*) FROM product_groups')->fetchColumn();
$rowCount = $pdo->query('SELECT COUNT(*) FROM product_group_rows')->fetchColumn();
$messageCount = $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
admin_header(admin_t('Dashboard'));
?>
<div class="stats-grid">
    <div><?php echo h(admin_t('News')); ?>: <?php echo (int)$newsCount; ?></div>
    <div><?php echo h(admin_t('Categories')); ?>: <?php echo (int)$categoryCount; ?></div>
    <div><?php echo h(admin_t('Groups')); ?>: <?php echo (int)$groupCount; ?></div>
    <div><?php echo h(admin_t('Rows')); ?>: <?php echo (int)$rowCount; ?></div>
    <div><?php echo h(admin_t('Messages')); ?>: <?php echo (int)$messageCount; ?></div>
</div>
<?php admin_footer(); ?>
