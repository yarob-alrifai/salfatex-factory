<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$groupId = (int)($_GET['group_id'] ?? 0);
$groupStmt = $pdo->prepare('SELECT * FROM product_groups WHERE id = :id');
$groupStmt->execute(['id' => $groupId]);
$group = $groupStmt->fetch();
if (!$group) {
    header('Location: variants_list.php');
    exit;
}
$columns = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
$columns->execute(['id' => $groupId]);
$columns = $columns->fetchAll();
admin_header('Add variant');
?>
<form method="post" action="variant_save.php">
    <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
    <p>Group: <?php echo h($group['group_title']); ?></p>
    <?php foreach ($columns as $column): ?>
        <label><?php echo h($column['column_name']); ?><textarea name="values[<?php echo (int)$column['id']; ?>]" rows="2"></textarea></label>
    <?php endforeach; ?>
    <button class="btn" type="submit">Save variant</button>
</form>
<?php admin_footer(); ?>
