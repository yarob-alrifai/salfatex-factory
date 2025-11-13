<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = get_all_categories();
site_header('Каталог продукции');
?>
<section class="categories page bg-white py-16">
    <div class="mx-auto max-w-6xl space-y-6 px-6">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Каталог</p>
            <h1 class="text-3xl font-semibold text-slate-900">Категории продукции</h1>
            <p class="text-slate-600">Выберите нужное направление, чтобы ознакомиться с ассортиментом.</p>
        </div>
        <?php if ($categories): ?>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card flex h-full flex-col gap-4 rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand hover:shadow-glow">
                        <div class="category-card__media overflow-hidden rounded-2xl">
                            <?php if (!empty($category['hero_image'])): ?>
                                <img class="h-52 w-full object-cover" src="<?php echo h($category['hero_image']); ?>" alt="<?php echo h($category['name']); ?>">
                            <?php else: ?>
                                <img class="h-52 w-full object-cover" src="images/placeholder.svg" alt="<?php echo h($category['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-900"><?php echo h($category['name']); ?></h3>
                        <?php if (!empty($category['description'])): ?>
                            <p class="text-sm text-slate-600"><?php echo h(mb_substr(strip_tags($category['description']), 0, 150)); ?>...</p>
                        <?php else: ?>
                            <p class="text-sm text-slate-600">Гибкая настройка плотности, слоистости и рисунка тиснения.</p>
                        <?php endif; ?>
                        <div class="flex flex-1 items-end">
                            <a class="btn-secondary inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 font-semibold text-slate-700 transition hover:border-brand hover:text-brand" href="category.php?category=<?php echo h($category['slug']); ?>">Перейти</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600">
                <p>Категории еще не добавлены.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
