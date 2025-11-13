<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$news = $pdo->query('SELECT * FROM news ORDER BY created_at DESC')->fetchAll();
admin_header('News');
?>
<a class="btn" href="news_add.php">Add news</a>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Title</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($news as $item): ?>
        <tr>
            <td><?php echo (int)$item['id']; ?></td>
            <td><?php echo h($item['title']); ?></td>
            <td><?php echo h($item['created_at']); ?></td>
            <td>
                <a href="news_edit.php?id=<?php echo (int)$item['id']; ?>">Edit</a> |
                <a href="news_delete.php?id=<?php echo (int)$item['id']; ?>" onclick="return confirm('Delete news?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
