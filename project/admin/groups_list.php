<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
$groups = $pdo->query('SELECT pg.*, pc.name AS category_name FROM product_groups pg LEFT JOIN product_categories pc ON pg.category_id = pc.id ORDER BY pg.created_at DESC')->fetchAll();
admin_header(admin_t('Product groups'));
?>
<a class="btn" href="group_add.php"><?php echo h(admin_t('Add group')); ?></a>
<table class="admin-table">
    <thead><tr><th><?php echo h(admin_t('ID')); ?></th><th><?php echo h(admin_t('Category')); ?></th><th><?php echo h(admin_t('Title')); ?></th><th><?php echo h(admin_t('Actions')); ?></th></tr></thead>
    <tbody>
    <?php foreach ($groups as $group): ?>
        <tr>
            <td><?php echo (int)$group['id']; ?></td>
            <td><?php echo h($group['category_name'] ?? '—'); ?></td>
            <td><?php echo h($group['group_title']); ?></td>
            <td>
                <a href="group_edit.php?id=<?php echo (int)$group['id']; ?>"><?php echo h(admin_t('Edit')); ?></a> |
                <a href="group_delete.php?id=<?php echo (int)$group['id']; ?>" onclick="return confirm(<?php echo h(json_encode(admin_t('Delete group?'), JSON_UNESCAPED_UNICODE)); ?>);"><?php echo h(admin_t('Delete')); ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
