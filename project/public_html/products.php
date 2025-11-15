<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = get_all_categories();
$meta = [
    'title' => 'Каталог бумажной продукции — Salfatex',
    'description' => 'Категории салфеток, полотенец и туалетной бумаги с характеристиками и вариантами упаковки.',
    'canonical' => site_url('products.php'),
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Каталог продукции',
            'url' => site_url('products.php')
        ]
    ]
];
site_header('Каталог продукции', $meta);
?>
<section class="relative isolate overflow-hidden bg-slate-950 py-20 text-white">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-sky-600/60 via-indigo-500/30 to-transparent"></div>
    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 text-center">
        <div class="space-y-5">
            <p class="text-xs font-semibold uppercase tracking-[0.45em] text-sky-200">Каталог продукции</p>
            <h1 class="text-4xl font-semibold leading-tight md:text-5xl">Готовые решения из бумаги для HoReCa и ритейла</h1>
            <p class="text-base text-slate-200 md:text-lg">Соберите индивидуальную линейку салфеток, полотенец и туалетной бумаги с нужной плотностью, цветом и упаковкой. Мы поможем подобрать технологию под ваши требования.</p>
        </div>
        <div class="mx-auto flex w-full flex-col gap-4 rounded-3xl border border-white/10 bg-white/5 p-6 text-left backdrop-blur md:flex-row md:items-center">
            <div>
                <p class="text-sm uppercase tracking-[0.35em] text-slate-200">Фильтр по категориям</p>
                <p class="text-lg font-semibold text-white">Навигация по всему ассортименту</p>
            </div>
            <?php if ($categories): ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($categories as $category): ?>
                        <a class="rounded-full border border-white/40 px-4 py-2 text-sm font-medium text-white/90 transition hover:border-white hover:text-white" href="#category-<?php echo h($category['slug']); ?>"><?php echo h($category['name']); ?></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-white/70">Категории скоро появятся.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl space-y-12 px-6">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-200/80 bg-slate-50/60 p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-slate-500">Производство</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">24/7</p>
                <p class="mt-2 text-sm text-slate-600">Линии непрерывно выпускают продукцию, поэтому отгрузка возможна в короткие сроки.</p>
            </div>
            <div class="rounded-3xl border border-slate-200/80 bg-slate-50/60 p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-slate-500">Ассортимент</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">50+ SKU</p>
                <p class="mt-2 text-sm text-slate-600">Готовые рецептуры и возможность кастомизации цвета, слоев, тиснения.</p>
            </div>
            <div class="rounded-3xl border border-slate-200/80 bg-slate-50/60 p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.4em] text-slate-500">Контроль</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">ISO 9001</p>
                <p class="mt-2 text-sm text-slate-600">Каждая партия сопровождается паспортом качества и лабораторными протоколами.</p>
            </div>
        </div>

        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Категории</p>
            <h2 class="text-3xl font-semibold text-slate-900">Выберите направление поставки</h2>
            <p class="text-slate-600">Каждая карточка раскрывает ключевые особенности продукции: плотность, количество слоев, форматы упаковки.</p>
        </div>

        <?php if ($categories): ?>
            <div class="grid gap-8 lg:grid-cols-2">
                <?php foreach ($categories as $category): ?>
                    <article id="category-<?php echo h($category['slug']); ?>" class="group grid gap-6 rounded-3xl border border-slate-200 bg-gradient-to-br from-white via-white to-slate-50/60 p-6 shadow-[0_30px_120px_-60px_rgba(15,23,42,0.6)] transition hover:-translate-y-1 hover:border-brand/60">
                        <div class="overflow-hidden rounded-2xl border border-slate-100">
                            <?php if (!empty($category['hero_image'])): ?>
                                <?php echo render_picture($category['hero_image'], $category['hero_image_alt'] ?: $category['name'], ['class' => 'h-64 w-full object-cover transition duration-500 group-hover:scale-[1.02]']); ?>
                            <?php else: ?>
                                <div class="flex h-64 w-full items-center justify-center bg-slate-100 text-sm text-slate-500">Нет изображения</div>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-700">Серия</span>
                                <span class="text-sm text-slate-500">Артикул: <?php echo strtoupper(h($category['slug'])); ?></span>
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-2xl font-semibold text-slate-900"><?php echo h($category['name']); ?></h3>
                                <?php if (!empty($category['description'])): ?>
                                    <p class="text-base text-slate-600"><?php echo h(mb_substr(strip_tags($category['description']), 0, 180)); ?>...</p>
                                <?php else: ?>
                                    <p class="text-base text-slate-600">Сбалансированная линейка решений с возможностью брендирования упаковки, печати логотипов и настройки характеристик.</p>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm text-slate-500">
                                <span class="rounded-full border border-slate-200 px-3 py-1">2-4 слоя</span>
                                <span class="rounded-full border border-slate-200 px-3 py-1">Цветная и белая бумага</span>
                                <span class="rounded-full border border-slate-200 px-3 py-1">Тиснение по запросу</span>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm text-slate-500">Доступны образцы и консультирование технолога.</p>
                                <a class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800" href="category.php?category=<?php echo h($category['slug']); ?>">Смотреть категорию</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600">
                <p>Категории еще не добавлены. Добавьте их в админ-панели, чтобы показать ассортимент.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="bg-slate-950 py-20 text-white">
    <div class="mx-auto max-w-5xl space-y-6 px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-200">Запрос коммерческого предложения</p>
        <h2 class="text-3xl font-semibold leading-tight">Получите подборку решений и расчет стоимости</h2>
        <p class="text-base text-slate-200">Отправьте заявку и наш менеджер подготовит индивидуальный каталог с оптимальными параметрами продукции под ваш канал продаж.</p>
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
            <a class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100" href="contact.php">Оставить заявку</a>
            <a class="inline-flex items-center justify-center rounded-2xl border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:border-white" href="tel:+78000000000">Связаться по телефону</a>
        </div>
    </div>
</section>
<?php site_footer(); ?>
