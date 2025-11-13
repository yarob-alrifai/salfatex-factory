<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = allowed_categories();
$categoryKey = sanitize_category($_GET['category'] ?? null);
if (!$categoryKey) {
    http_response_code(404);
    echo 'Категория не найдена';
    exit;
}
$categoryTitle = $categories[$categoryKey];
$groupStmt = $pdo->prepare('SELECT * FROM product_groups WHERE category = :category ORDER BY created_at DESC');
$groupStmt->execute(['category' => $categoryKey]);
$groups = $groupStmt->fetchAll();
site_header('Категория: ' . $categoryTitle, ['title' => $categoryTitle]);
?>
<section class="category-intro">
    <div class="container">
        <h1><?php echo h($categoryTitle); ?></h1>
        <p>Узнайте подробные характеристики каждой серии продукции. Таблицы обновляются автоматически через админ-панель.</p>
    </div>
</section>
<div class="container">
    <?php if ($groups): ?>
        <?php foreach ($groups as $group): ?>
            <?php
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
            <article class="product-group">
                <h2><?php echo h($group['group_title']); ?></h2>
                <div class="group-body">
                    <div class="group-gallery" data-gallery>
                        <?php if ($images): ?>
                            <?php foreach ($images as $image): ?>
                                <img src="uploads/groups/<?php echo h($image['image_path']); ?>" alt="<?php echo h($group['group_title']); ?>">
                            <?php endforeach; ?>
                        <?php else: ?>
                            <img src="images/placeholder.jpg" alt="<?php echo h($group['group_title']); ?>">
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
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>В этой категории пока нет групп товаров.</p>
    <?php endif; ?>
</div>
<?php site_footer(); ?>
