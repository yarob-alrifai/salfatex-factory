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
$columnsByGroup = [];
$rowsByGroup = [];
$cellMapByGroup = [];
$groupImages = [];

if ($groups) {
    $columnStmt = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
    $rowStmt = $pdo->prepare('SELECT * FROM product_group_rows WHERE group_id = :id ORDER BY row_index');
    $cellsStmt = $pdo->prepare('SELECT c.id as column_id, r.id as row_id, cell.value FROM product_group_cells cell JOIN product_group_rows r ON cell.row_id = r.id JOIN product_group_columns c ON cell.column_id = c.id WHERE r.group_id = :id');
    $imagesStmt = $pdo->prepare('SELECT * FROM product_group_images WHERE group_id = :id ORDER BY id');

    foreach ($groups as $group) {
        $columnStmt->execute(['id' => $group['id']]);
        $columnsByGroup[$group['id']] = $columnStmt->fetchAll();

        $rowStmt->execute(['id' => $group['id']]);
        $rowsByGroup[$group['id']] = $rowStmt->fetchAll();

        $cellsStmt->execute(['id' => $group['id']]);
        $cellMap = [];
        foreach ($cellsStmt as $cell) {
            $cellMap[$cell['row_id']][$cell['column_id']] = $cell['value'];
        }
        $cellMapByGroup[$group['id']] = $cellMap;

        $imagesStmt->execute(['id' => $group['id']]);
        $groupImages[$group['id']] = $imagesStmt->fetchAll();
    }
}
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
$breadcrumbs = [
    ['label' => 'Главная', 'href' => site_url('index.php'), 'icon' => 'home'],
    ['label' => 'Каталог', 'href' => site_url('products.php')],
    ['label' => $category['name'], 'current' => true],
];
?>
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 py-16 text-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-12 px-4 sm:px-6 lg:flex-row lg:items-center lg:px-8">
        <div class="flex-1 space-y-6">
            <?php echo render_breadcrumbs($breadcrumbs, ['class' => 'text-slate-300']); ?>
            <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-1 text-xs uppercase tracking-[0.2em] text-slate-300">
                <span class="size-2 rounded-full bg-emerald-400"></span>
                Категория каталога
            </div>
            <div class="space-y-4">
                <p class="text-sm font-semibold text-emerald-300">Каталог продукции</p>
                <h1 class="text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                    <?php echo h($h1); ?>
                </h1>
                <?php if (!empty($category['description'])): ?>
                    <div class="prose prose-invert max-w-none text-base text-slate-200">
                        <?php echo safe_html($category['description']); ?>
                    </div>
                <?php else: ?>
                    <p class="text-base text-slate-300">Узнайте подробные характеристики каждой серии продукции. Таблицы обновляются автоматически через админ-панель.</p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap gap-6 text-sm text-slate-300">
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-400"></span>
                    Обновляется через админ-панель
                </div>
                <div class="flex items-center gap-2">
                    <span class="size-2 rounded-full bg-blue-400"></span>
                    Актуальные серии и характеристики
                </div>
            </div>
        </div>
        <div class="flex-1">
            <div class="relative overflow-hidden rounded-[32px] border border-white/5 bg-white/5 shadow-2xl shadow-emerald-900/30">
                <?php if (!empty($category['hero_image'])): ?>
                    <?php echo render_picture($category['hero_image'], $category['hero_image_alt'] ?: $category['name'], ['class' => 'h-full w-full object-cover']); ?>
                <?php else: ?>
                    <div class="flex h-72 w-full items-center justify-center text-slate-200">Нет изображения</div>
                <?php endif; ?>
                <div class="pointer-events-none absolute inset-x-6 bottom-6 rounded-2xl bg-black/40 p-4 text-sm text-white backdrop-blur">
                    <p class="font-medium">Наша визуальная библиотека</p>
                    <p class="text-slate-200">Выберите серию продукции, чтобы увидеть детальные таблицы и изображения.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php if ($categoryGallery): ?>
    <section class="bg-white py-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between pb-6">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-emerald-500">галерея</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Атмосфера бренда</h2>
                </div>
                <div class="text-sm text-slate-500">Перелистывайте изображения</div>
            </div>
            <div class="grid gap-6 md:grid-cols-3" data-gallery>
                <?php foreach ($categoryGallery as $image): ?>
                    <div class="group relative overflow-hidden rounded-3xl border border-slate-100 bg-slate-50/60 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                        <?php echo render_picture($image['image_path'], $image['alt_text'] ?: $category['name'], ['class' => 'h-56 w-full object-cover transition duration-500 group-hover:scale-105']); ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 to-transparent"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-6xl space-y-12 px-4 sm:px-6 lg:px-8">
        <?php if ($groups): ?>
            <div class="space-y-10">
                <?php foreach ($groups as $group): ?>
                    <?php $columns = $columnsByGroup[$group['id']] ?? []; ?>
                    <?php $rows = $rowsByGroup[$group['id']] ?? []; ?>
                    <?php $cellMap = $cellMapByGroup[$group['id']] ?? []; ?>
                    <?php $galleryImages = $groupImages[$group['id']] ?? []; ?>
                    <article class="overflow-hidden rounded-[32px] border border-slate-100 bg-white shadow-lg shadow-slate-200/60">
                        <div class="grid gap-8 lg:grid-cols-[minmax(0,_0.9fr)_minmax(0,_1.1fr)]">
                            <div class="relative">
                                <div class="h-full overflow-hidden rounded-[32px] border-b border-slate-100 bg-slate-50 lg:border-b-0 lg:border-r">
                                    <?php if (!empty($group['main_image'])): ?>
                                        <?php echo render_picture($group['main_image'], $group['main_image_alt'] ?: $group['group_title'], ['class' => 'h-full w-full object-cover']); ?>
                                    <?php else: ?>
                                        <div class="flex h-full min-h-[260px] items-center justify-center text-sm text-slate-500">Нет изображения</div>
                                    <?php endif; ?>
                                </div>
                                <div class="pointer-events-none absolute inset-x-6 bottom-6 hidden rounded-2xl bg-black/40 px-4 py-2 text-xs font-medium text-white backdrop-blur lg:block">
                                    Серия: <?php echo h($group['group_title']); ?>
                                </div>
                            </div>
                            <div class="flex flex-col gap-6 p-8">
                                <div class="flex flex-wrap items-center gap-3 text-xs uppercase tracking-[0.3em] text-slate-400">
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">серия</span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700"><?php echo h($group['category_name'] ?? $category['name']); ?></span>
                                </div>
                                <header class="space-y-3">
                                    <h2 class="text-2xl font-semibold text-slate-900"><?php echo h($group['group_title']); ?></h2>
                                    <?php if (!empty($group['article'])): ?>
                                        <p class="text-sm font-medium text-slate-500">Артикул: <?php echo h($group['article']); ?></p>
                                    <?php endif; ?>
                                </header>
                                <div class="prose max-w-none text-sm text-slate-600">
                                    <?php echo safe_html($group['left_description']); ?>
                                </div>
                                <?php if ($columns && $rows): ?>
                                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                                        <div class="overflow-auto">
                                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                                    <tr>
                                                        <?php foreach ($columns as $column): ?>
                                                            <th scope="col" class="px-4 py-3"><?php echo h($column['column_name']); ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 bg-white">
                                                    <?php foreach ($rows as $row): ?>
                                                        <tr class="hover:bg-slate-50/80">
                                                            <?php foreach ($columns as $column): ?>
                                                                <?php $value = $cellMap[$row['id']][$column['id']] ?? ''; ?>
                                                                <td class="whitespace-pre-line px-4 py-3 text-sm text-slate-700" data-label="<?php echo h($column['column_name']); ?>"><?php echo nl2br(h($value)); ?></td>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($group['seo_text'])): ?>
                                    <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-600">
                                        <?php echo safe_html($group['seo_text']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($galleryImages): ?>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-xs uppercase tracking-[0.25em] text-slate-400">
                                            <span>галерея продукта</span>
                                            <span class="text-slate-500">Изображения коллекции</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4" data-gallery>
                                            <?php foreach ($galleryImages as $image): ?>
                                                <div class="group relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                                                    <?php echo render_picture($image['image_path'], $image['alt_text'] ?: $group['group_title'], ['class' => 'h-28 w-full object-cover transition duration-300 group-hover:scale-105']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-500">
                В этой категории пока нет групп товаров.
            </div>
        <?php endif; ?>
        <?php if (!empty($category['seo_text'])): ?>
            <div class="prose max-w-none rounded-[32px] border border-slate-100 bg-white p-10 text-slate-600">
                <?php echo safe_html($category['seo_text']); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
