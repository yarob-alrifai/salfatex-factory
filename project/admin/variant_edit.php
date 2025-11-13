<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$groupId = (int)($_GET['group_id'] ?? 0);
$rowStmt = $pdo->prepare('SELECT * FROM product_group_rows WHERE id = :id AND group_id = :group_id');
$rowStmt->execute(['id' => $id, 'group_id' => $groupId]);
$row = $rowStmt->fetch();
if (!$row) {
    header('Location: variants_list.php');
    exit;
}
$groupStmt = $pdo->prepare('SELECT * FROM product_groups WHERE id = :id');
$groupStmt->execute(['id' => $groupId]);
$group = $groupStmt->fetch();
$columns = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
$columns->execute(['id' => $groupId]);
$columns = $columns->fetchAll();
$cellsStmt = $pdo->prepare('SELECT column_id, value FROM product_group_cells WHERE row_id = :row_id');
$cellsStmt->execute(['row_id' => $id]);
$values = [];
foreach ($cellsStmt as $cell) {
    $values[$cell['column_id']] = $cell['value'];
}
admin_header('Edit variant');
?>
<form method="post" action="variant_update.php">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
    <p>Group: <?php echo h($group['group_title']); ?></p>
    <?php foreach ($columns as $column): ?>
        <label><?php echo h($column['column_name']); ?><textarea name="values[<?php echo (int)$column['id']; ?>]" rows="2"><?php echo h($values[$column['id']] ?? ''); ?></textarea></label>
    <?php endforeach; ?>
    <button class="btn" type="submit">Update variant</button>
</form>
<?php admin_footer(); ?>
