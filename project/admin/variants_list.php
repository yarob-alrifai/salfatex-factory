<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$groups = $pdo->query('SELECT id, group_title FROM product_groups ORDER BY group_title')->fetchAll();
$selectedGroup = (int)($_GET['group_id'] ?? 0);
$rows = [];
$columns = [];
$cellMap = [];
if ($selectedGroup) {
    $columnStmt = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
    $columnStmt->execute(['id' => $selectedGroup]);
    $columns = $columnStmt->fetchAll();
    $rowStmt = $pdo->prepare('SELECT * FROM product_group_rows WHERE group_id = :id ORDER BY row_index');
    $rowStmt->execute(['id' => $selectedGroup]);
    $rows = $rowStmt->fetchAll();
    $cellsStmt = $pdo->prepare('SELECT * FROM product_group_cells WHERE row_id = :row_id');
    foreach ($rows as $row) {
        $cellsStmt->execute(['row_id' => $row['id']]);
        $cellMap[$row['id']] = $cellsStmt->fetchAll();
    }
}
admin_header('Variants');
?>
<form method="get">
    <label>Select group
        <select name="group_id" onchange="this.form.submit()">
            <option value="0">Choose...</option>
            <?php foreach ($groups as $group): ?>
                <option value="<?php echo (int)$group['id']; ?>" <?php echo $selectedGroup === (int)$group['id'] ? 'selected' : ''; ?>><?php echo h($group['group_title']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>
<?php if ($selectedGroup): ?>
    <a class="btn" href="variant_add.php?group_id=<?php echo $selectedGroup; ?>">Add variant</a>
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th>
                <?php foreach ($columns as $column): ?>
                    <th><?php echo h($column['column_name']); ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo (int)$row['row_index']; ?></td>
                    <?php foreach ($columns as $column): ?>
                        <?php
                        $value = '';
                        if (!empty($cellMap[$row['id']])) {
                            foreach ($cellMap[$row['id']] as $cell) {
                                if ((int)$cell['column_id'] === (int)$column['id']) {
                                    $value = $cell['value'];
                                    break;
                                }
                            }
                        }
                        ?>
                        <td><?php echo h($value); ?></td>
                    <?php endforeach; ?>
                    <td>
                        <a href="variant_edit.php?id=<?php echo (int)$row['id']; ?>&group_id=<?php echo $selectedGroup; ?>">Edit</a> |
                        <a href="variant_delete.php?id=<?php echo (int)$row['id']; ?>&group_id=<?php echo $selectedGroup; ?>" onclick="return confirm('Delete?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php admin_footer(); ?>
