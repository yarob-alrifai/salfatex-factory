<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$categories = $pdo->query('SELECT pc.*, COUNT(pg.id) AS products_count FROM product_categories pc LEFT JOIN product_groups pg ON pg.category_id = pc.id GROUP BY pc.id ORDER BY pc.name')->fetchAll();
admin_header('Categories');
?>
<a class="btn" href="category_add.php">Add category</a>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Slug</th>
            <th>Name</th>
            <th>Products</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo (int)$category['id']; ?></td>
            <td><?php echo h($category['slug']); ?></td>
            <td><?php echo h($category['name']); ?></td>
            <td><?php echo (int)($category['products_count'] ?? 0); ?></td>
            <td>
                <a href="category_edit.php?id=<?php echo (int)$category['id']; ?>">Edit</a> |
                <a href="category_delete.php?id=<?php echo (int)$category['id']; ?>" onclick="return confirm('Delete category?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
