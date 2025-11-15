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
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 py-16 text-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-12 px-4 sm:px-6 lg:flex-row lg:items-center lg:px-8">
        <div class="flex-1 space-y-6">
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
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($groups as $group): ?>
                    <a href="group.php?slug=<?php echo h($group['slug']); ?>" class="group flex flex-col overflow-hidden rounded-[28px] border border-slate-100 bg-white/90 p-6 shadow-lg shadow-slate-200/60 transition hover:-translate-y-1 hover:shadow-emerald-100 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-emerald-200" aria-label="Открыть серию <?php echo h($group['group_title']); ?>">
                        <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-slate-50">
                            <?php if (!empty($group['main_image'])): ?>
                                <?php echo render_picture($group['main_image'], $group['main_image_alt'] ?: $group['group_title'], ['class' => 'aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105']); ?>
                            <?php else: ?>
                                <div class="flex aspect-[4/3] w-full items-center justify-center text-sm text-slate-500">Нет изображения</div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-6 flex flex-col gap-4 text-slate-700">
                            <div class="space-y-2">
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">серия</p>
                                <h2 class="text-xl font-semibold text-slate-900">
                                    <?php echo h($group['group_title']); ?>
                                </h2>
                            </div>
                            <div class="prose max-w-none text-sm text-slate-600">
                                <?php echo safe_html($group['left_description']); ?>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-between text-sm font-semibold text-emerald-600">
                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white">Открыть</span>
                            <svg class="size-5 text-current transition group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7m0 0H9m8 0v8" />
                            </svg>
                        </div>
                    </a>
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
