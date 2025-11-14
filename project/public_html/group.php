<?php
require_once __DIR__ . '/../inc/helpers.php';
$slug = $_GET['slug'] ?? '';
$group = get_group_by_slug($slug);
if (!$group) {
    http_response_code(404);
    echo 'Группа не найдена';
    exit;
}
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
$canonical = $group['canonical_url'] ?: site_url('group.php?slug=' . $group['slug']);
$meta = [
    'title' => $group['meta_title'] ?: $group['group_title'],
    'description' => $group['meta_description'] ?: mb_substr(strip_tags($group['left_description']), 0, 160),
    'keywords' => $group['meta_keywords'] ?: $group['group_title'],
    'canonical' => $canonical,
    'og_title' => $group['og_title'] ?: $group['group_title'],
    'og_description' => $group['og_description'] ?: mb_substr(strip_tags($group['left_description']), 0, 160),
    'og_image' => $group['og_image'] ?: $group['main_image'],
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $group['group_title'],
            'category' => $group['category_name'],
            'description' => strip_tags($group['left_description']),
            'image' => $group['og_image'] ?: $group['main_image'],
            'url' => $canonical
        ]
    ]
];
site_header($group['group_title'], $meta);
$h1 = $group['h1'] ?: $group['group_title'];
?>
<section class="category-intro">
    <div class="container">
        <a class="back-link" href="category.php?category=<?php echo h($group['category_slug']); ?>">&larr; Назад к категории</a>
        <h1><?php echo h($h1); ?></h1>
        <p>Категория: <?php echo h($group['category_name']); ?></p>
    </div>
</section>
<div class="container">
    <div class="group-body">
        <div class="group-gallery" data-gallery>
            <?php if (!empty($group['main_image'])): ?>
                <?php echo render_picture($group['main_image'], $group['main_image_alt'] ?: $group['group_title'], ['class' => 'group-gallery__main']); ?>
            <?php endif; ?>
            <?php if ($images): ?>
                <?php foreach ($images as $image): ?>
                    <?php echo render_picture($image['image_path'], $image['alt_text'] ?: $group['group_title'], ['class' => 'w-32 rounded-xl object-cover']); ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if (empty($group['main_image']) && !$images): ?>
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-slate-500">Нет изображений</div>
            <?php endif; ?>
        </div>
        <div class="group-description prose prose-slate max-w-none">
            <?php echo safe_html($group['left_description']); ?>
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
        <div class="group-seo prose prose-slate max-w-none">
            <?php echo safe_html($group['seo_text']); ?>
        </div>
    <?php endif; ?>
</div>
<?php site_footer(); ?>
