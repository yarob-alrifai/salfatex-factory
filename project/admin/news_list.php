<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
$news = $pdo->query('SELECT * FROM news ORDER BY created_at DESC')->fetchAll();
admin_header(admin_t('News'));
?>
<a class="btn" href="news_add.php"><?php echo h(admin_t('Add news')); ?></a>
<table class="admin-table">
    <thead><tr><th><?php echo h(admin_t('ID')); ?></th><th><?php echo h(admin_t('Title')); ?></th><th><?php echo h(admin_t('Date')); ?></th><th><?php echo h(admin_t('Actions')); ?></th></tr></thead>
    <tbody>
    <?php foreach ($news as $item): ?>
        <tr>
            <td><?php echo (int)$item['id']; ?></td>
            <td><?php echo h($item['title']); ?></td>
            <td><?php echo h($item['created_at']); ?></td>
            <td>
                <a href="news_edit.php?id=<?php echo (int)$item['id']; ?>"><?php echo h(admin_t('Edit')); ?></a> |
                <a href="news_delete.php?id=<?php echo (int)$item['id']; ?>" onclick="return confirm(<?php echo h(json_encode(admin_t('Delete news?'), JSON_UNESCAPED_UNICODE)); ?>);"><?php echo h(admin_t('Delete')); ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
