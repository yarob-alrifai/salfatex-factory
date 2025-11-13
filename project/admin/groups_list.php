<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$groups = $pdo->query('SELECT pg.*, pc.name AS category_name FROM product_groups pg LEFT JOIN product_categories pc ON pg.category_id = pc.id ORDER BY pg.created_at DESC')->fetchAll();
admin_header('Product groups');
?>
<a class="btn" href="group_add.php">Add group</a>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Category</th><th>Title</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($groups as $group): ?>
        <tr>
            <td><?php echo (int)$group['id']; ?></td>
            <td><?php echo h($group['category_name'] ?? '—'); ?></td>
            <td><?php echo h($group['group_title']); ?></td>
            <td>
                <a href="group_edit.php?id=<?php echo (int)$group['id']; ?>">Edit</a> |
                <a href="group_delete.php?id=<?php echo (int)$group['id']; ?>" onclick="return confirm('Delete group?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
