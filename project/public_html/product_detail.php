<?php
require_once __DIR__ . '/../inc/helpers.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Группа не найдена';
    exit;
}
$groupStmt = $pdo->prepare('SELECT pg.*, pc.name AS category_name, pc.slug AS category_slug FROM product_groups pg JOIN product_categories pc ON pg.category_id = pc.id WHERE pg.id = :id');
$groupStmt->execute(['id' => $id]);
$group = $groupStmt->fetch();
if (!$group) {
    http_response_code(404);
    echo 'Группа не найдена';
    exit;
}
$categoryTitle = $group['category_name'] ?? 'Продукция';
$meta = ['title' => $group['group_title'] . ' — ' . $categoryTitle];
site_header($group['group_title'], $meta);
$imageStmt = $pdo->prepare('SELECT * FROM product_group_images WHERE group_id = :id');
$imageStmt->execute(['id' => $group['id']]);
$images = $imageStmt->fetchAll();
$columnStmt = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
$columnStmt->execute(['id' => $group['id']]);
$columns = $columnStmt->fetchAll();
$rowStmt = $pdo->prepare('SELECT * FROM product_group_rows WHERE group_id = :id ORDER BY row_index');
$rowStmt->execute(['id' => $group['id']]);
$rows = $rowStmt->fetchAll();
$cellsStmt = $pdo->prepare('SELECT c.id as column_id, r.id as row_id, cell.value FROM product_group_cells cell JOIN product_group_rows r ON cell.row_id = r.id JOIN product_group_columns c ON cell.column_id = c.id WHERE r.group_id = :id');
$cellsStmt->execute(['id' => $group['id']]);
$cellMap = [];
foreach ($cellsStmt as $cell) {
    $cellMap[$cell['row_id']][$cell['column_id']] = $cell['value'];
}
?>
<section class="category-intro">
    <div class="container">
        <a class="back-link" href="category.php?category=<?php echo h($group['category_slug']); ?>">&larr; Назад к категории</a>
        <h1><?php echo h($group['group_title']); ?></h1>
        <p>Категория: <?php echo h($categoryTitle); ?></p>
    </div>
</section>
<div class="container">
    <div class="group-body">
        <div class="group-gallery" data-gallery>
            <?php if (!empty($group['main_image'])): ?>
                <img class="group-gallery__main" src="uploads/groups/<?php echo h($group['main_image']); ?>" alt="<?php echo h($group['group_title']); ?>">
            <?php endif; ?>
            <?php if ($images): ?>
                <?php foreach ($images as $image): ?>
                    <img src="uploads/groups/<?php echo h($image['image_path']); ?>" alt="<?php echo h($group['group_title']); ?>">
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (empty($group['main_image']) && !$images): ?>
                <img src="images/placeholder.svg" alt="<?php echo h($group['group_title']); ?>">
            <?php endif; ?>
        </div>
        <div class="group-description">
            <?php echo nl2br(h($group['left_description'])); ?>
        </div>
    </div>
    <?php if ($columns && $rows): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th><?php echo h($column['column_name']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <?php $value = $cellMap[$row['id']][$column['id']] ?? ''; ?>
                                <td><?php echo nl2br(h($value)); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if (!empty($group['seo_text'])): ?>
        <div class="group-seo">
            <?php echo nl2br(h($group['seo_text'])); ?>
        </div>
    <?php endif; ?>
</div>
<?php site_footer(); ?>
