<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
$categories = $pdo->query('SELECT pc.*, COUNT(pg.id) AS products_count FROM product_categories pc LEFT JOIN product_groups pg ON pg.category_id = pc.id GROUP BY pc.id ORDER BY pc.sort_order ASC, pc.name')->fetchAll();
admin_header(admin_t('Categories'));
?>
<a class="btn" href="category_add.php"><?php echo h(admin_t('Add category')); ?></a>
<table class="admin-table">
    <thead>
        <tr>
            <th><?php echo h(admin_t('ID')); ?></th>
            <th><?php echo h(admin_t('Slug')); ?></th>
            <th><?php echo h(admin_t('Name')); ?></th>
            <th><?php echo h(admin_t('Order')); ?></th>
            <th><?php echo h(admin_t('Products')); ?></th>
            <th><?php echo h(admin_t('Actions')); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo (int)$category['id']; ?></td>
            <td><?php echo h($category['slug']); ?></td>
            <td><?php echo h($category['name']); ?></td>
            <td><?php echo (int)$category['sort_order']; ?></td>
            <td><?php echo (int)($category['products_count'] ?? 0); ?></td>
            <td>
                <a href="category_edit.php?id=<?php echo (int)$category['id']; ?>"><?php echo h(admin_t('Edit')); ?></a> |
                <a href="category_delete.php?id=<?php echo (int)$category['id']; ?>" onclick="return confirm(<?php echo h(json_encode(admin_t('Delete category?'), JSON_UNESCAPED_UNICODE)); ?>);"><?php echo h(admin_t('Delete')); ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
