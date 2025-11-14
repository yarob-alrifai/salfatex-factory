<?php
require_once __DIR__ . '/../inc/helpers.php';
$slug = $_GET['category'] ?? '';
$category = get_category_by_slug($slug);
if (!$category) {
    http_response_code(404);
    echo 'Категория не найдена';
    exit;
}
$groupStmt = $pdo->prepare('SELECT * FROM product_groups WHERE category_id = :category ORDER BY created_at DESC');
$groupStmt->execute(['category' => $category['id']]);
$groups = $groupStmt->fetchAll();
$galleryStmt = $pdo->prepare('SELECT * FROM product_category_images WHERE category_id = :id');
$galleryStmt->execute(['id' => $category['id']]);
$categoryGallery = $galleryStmt->fetchAll();
$canonical = $category['canonical_url'] ?: site_url('category.php?category=' . urlencode($category['slug']));
$itemList = [];
foreach ($groups as $index => $groupItem) {
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $groupItem['group_title'],
        'url' => site_url('group.php?slug=' . $groupItem['slug'])
    ];
}
$meta = [
    'title' => $category['meta_title'] ?: $category['name'],
    'description' => $category['meta_description'] ?: ($category['description'] ? mb_substr(strip_tags($category['description']), 0, 160) : 'Каталог продукции'),
    'keywords' => $category['meta_keywords'] ?: 'продукция, салфетки, категории',
    'canonical' => $canonical,
    'og_title' => $category['og_title'] ?: $category['name'],
    'og_description' => $category['og_description'] ?: ($category['description'] ? strip_tags($category['description']) : ''),
    'og_image' => $category['og_image'] ?: $category['hero_image'],
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category['name'],
            'description' => strip_tags($category['description'] ?? ''),
            'url' => $canonical,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $itemList
            ]
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Каталог', 'item' => site_url('products.php')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $category['name'], 'item' => $canonical]
            ]
        ]
    ]
];
site_header('Категория: ' . $category['name'], $meta);
$h1 = $category['h1'] ?: $category['name'];
?>
<section class="category-intro">
    <div class="container split">
        <div>
            <h1><?php echo h($h1); ?></h1>
            <?php if (!empty($category['description'])): ?>
                <div class="prose prose-slate max-w-none"><?php echo safe_html($category['description']); ?></div>
            <?php else: ?>
                <p>Узнайте подробные характеристики каждой серии продукции. Таблицы обновляются автоматически через админ-панель.</p>
            <?php endif; ?>
        </div>
        <div class="category-hero">
            <?php if (!empty($category['hero_image'])): ?>
                <?php echo render_picture($category['hero_image'], $category['hero_image_alt'] ?: $category['name'], ['class' => 'w-full rounded-2xl object-cover']); ?>
            <?php else: ?>
                <div class="flex h-64 w-full items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50 text-slate-500">Нет изображения</div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if ($categoryGallery): ?>
    <section class="category-gallery">
        <div class="container">
            <div class="gallery-track" data-gallery>
                <?php foreach ($categoryGallery as $image): ?>
                    <?php echo render_picture($image['image_path'], $image['alt_text'] ?: $category['name'], ['class' => 'w-full rounded-xl object-cover']); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
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
                <div class="flex items-center justify-between gap-3">
                    <h2><?php echo h($group['group_title']); ?></h2>
                    <a class="btn-secondary" href="group.php?slug=<?php echo h($group['slug']); ?>">Открыть страницу</a>
                </div>
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
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>В этой категории пока нет групп товаров.</p>
    <?php endif; ?>
    <?php if (!empty($category['seo_text'])): ?>
        <div class="category-seo prose prose-slate max-w-none py-8">
            <?php echo safe_html($category['seo_text']); ?>
        </div>
    <?php endif; ?>
</div>
<?php site_footer(); ?>
