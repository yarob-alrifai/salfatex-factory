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
$breadcrumbs = [
    ['label' => 'Главная', 'href' => site_url('index.php'), 'icon' => 'home'],
    ['label' => 'Каталог', 'href' => site_url('products.php')],
    ['label' => $group['category_name'], 'href' => site_url('category.php?category=' . urlencode($group['category_slug']))],
    ['label' => $group['group_title'], 'current' => true],
];
?>
<section class="relative isolate overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,.35),_transparent_55%)]"></div>
    <div class="container mx-auto px-4 py-16 relative space-y-6">
        <?php echo render_breadcrumbs($breadcrumbs, ['class' => 'text-slate-300']); ?>
        <div class="flex flex-wrap items-center gap-4 text-sm text-slate-200">
            <a class="inline-flex items-center gap-2 font-semibold text-cyan-300 transition hover:text-white" href="category.php?category=<?php echo h($group['category_slug']); ?>">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M12.707 15.707a1 1 0 01-1.414 0L6.586 11l4.707-4.707a1 1 0 00-1.414-1.414l-5.414 5.414a1 1 0 000 1.414l5.414 5.414a1 1 0 001.414-1.414z"/></svg>
                Назад к категории
            </a>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1 text-xs uppercase tracking-wider text-slate-100">
                <?php echo h($group['category_name']); ?>
            </span>
        </div>
        <div class="max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-3 text-xs uppercase tracking-[0.3em] text-slate-400">Коллекция</div>
            <h1 class="text-3xl font-semibold leading-tight text-white sm:text-4xl lg:text-5xl">
                <?php echo h($h1); ?>
            </h1>
            <p class="text-base text-slate-200 sm:text-lg">
                <?php echo h($group['hero_caption'] ?? 'Вдохновляющая коллекция с тщательно подобранными материалами и продуманной эргономикой.'); ?>
            </p>
        </div>
    </div>
</section>
<main class="bg-slate-50">
    <div class="container mx-auto px-4 py-16 space-y-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,_1.1fr)_minmax(0,_0.9fr)]">
            <div class="space-y-8" data-gallery>
                <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-100">
                    <div class="relative overflow-hidden rounded-3xl">
                        <?php if (!empty($group['main_image'])): ?>
                            <?php echo render_picture($group['main_image'], $group['main_image_alt'] ?: $group['group_title'], ['class' => 'h-[420px] w-full rounded-3xl object-cover']); ?>
                        <?php elseif ($images): ?>
                            <?php $first = $images[0]; ?>
                            <?php echo render_picture($first['image_path'], $first['alt_text'] ?: $group['group_title'], ['class' => 'h-[420px] w-full rounded-3xl object-cover']); ?>
                        <?php else: ?>
                            <div class="flex h-[420px] items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-100 text-slate-500">
                                Нет изображений
                            </div>
                        <?php endif; ?>
                        <div class="pointer-events-none absolute inset-x-6 bottom-6 rounded-2xl bg-black/40 px-4 py-3 text-xs font-medium text-white backdrop-blur">
                            Подробная галерея продукции
                        </div>
                    </div>
                </div>
                <?php if ($images && count($images) > 1): ?>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <?php foreach ($images as $image): ?>
                            <div class="group relative overflow-hidden rounded-2xl border border-transparent bg-white shadow-sm ring-1 ring-slate-100 transition hover:-translate-y-1 hover:border-cyan-200">
                                <?php echo render_picture($image['image_path'], $image['alt_text'] ?: $group['group_title'], ['class' => 'h-32 w-full object-cover transition duration-300 group-hover:scale-105']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="space-y-6">
                <div class="rounded-3xl bg-white/70 p-8 shadow-xl backdrop-blur">
                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">
                            Группа: <?php echo h($group['group_title']); ?>
                        </span>
                        <?php if (!empty($group['article'])): ?>
                            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">Артикул: <?php echo h($group['article']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="prose prose-slate mt-6 max-w-none">
                        <?php echo safe_html($group['left_description']); ?>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs uppercase tracking-widest text-slate-400">Категория</p>
                        <p class="mt-2 text-lg font-semibold text-slate-900"><?php echo h($group['category_name']); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs uppercase tracking-widest text-slate-400">URL</p>
                        <a class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-cyan-600 hover:text-cyan-800" href="<?php echo h($canonical); ?>" target="_blank" rel="noopener">
                            <?php echo h(parse_url($canonical, PHP_URL_PATH) ?: $canonical); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($columns && $rows): ?>
            <section class="space-y-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Характеристики</p>
                    <h2 class="text-2xl font-semibold text-slate-900">Технические параметры</h2>
                    <p class="text-sm text-slate-500">Сравнительная таблица с ключевыми данными по коллекции.</p>
                </div>
                <div class="overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-slate-100">
                    <div class="overflow-auto">
                        <table class="product-table min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <th scope="col" class="px-6 py-4"><?php echo h($column['column_name']); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php foreach ($rows as $row): ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <?php foreach ($columns as $column): ?>
                                            <?php $value = $cellMap[$row['id']][$column['id']] ?? ''; ?>
                                            <td class="whitespace-pre-line px-6 py-4 text-sm text-slate-700" data-label="<?php echo h($column['column_name']); ?>"><?php echo nl2br(h($value)); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        <?php if (!empty($group['seo_text'])): ?>
            <section class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-cyan-900 p-8 text-white shadow-2xl">
                <div>
                    <p class="text-xs uppercase tracking-[0.4em] text-cyan-200">SEO / Контент</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Экспертный обзор коллекции</h2>
                </div>
                <div class="prose prose-invert mt-6 max-w-none">
                    <?php echo safe_html($group['seo_text']); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php site_footer(); ?>
