<?php
require_once __DIR__ . '/../inc/helpers.php';
$stmt = $pdo->query('SELECT * FROM news ORDER BY created_at DESC');
$news = $stmt->fetchAll();
$totalNews = count($news);
$featured = $news[0] ?? null;
$otherNews = $featured ? array_slice($news, 1) : $news;
$itemList = [];
foreach ($news as $index => $article) {
    $itemList[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $article['title'],
        'url' => site_url('news_item.php?slug=' . $article['slug'])
    ];
}
$meta = [
    'title' => 'Новости фабрики',
    'description' => 'Обновления производства и новые продукты фабрики Salfatex.',
    'canonical' => site_url('news.php'),
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Новости',
            'url' => site_url('news.php'),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $itemList
            ]
        ]
    ]
];
site_header('Новости фабрики', $meta);
$breadcrumbs = [
    ['label' => 'Главная', 'href' => site_url('index.php'), 'icon' => 'home'],
    ['label' => 'Новости', 'href' => site_url('news.php'), 'current' => true],
];
$newsBreadcrumbOptions = [
    'class' => 'text-slate-500',
    'link_class' => 'inline-flex items-center text-slate-600 hover:text-slate-900',
    'home_link_class' => 'inline-flex items-center text-slate-600 hover:text-slate-900',
    'current_class' => 'inline-flex items-center text-slate-400',
    'separator_class' => 'size-3.5 text-slate-400',
];
?>

<section class="relative overflow-hidden bg-gradient-to-br from-sky-50 via-white to-white pb-16 pt-24">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute left-12 top-10 h-72 w-72 rounded-full bg-sky-100 blur-3xl"></div>
        <div class="absolute right-10 top-1/3 h-80 w-80 rounded-full bg-cyan-100 blur-3xl"></div>
    </div>
    <div class="relative mx-auto max-w-6xl px-6">
        <?php echo render_breadcrumbs($breadcrumbs, $newsBreadcrumbOptions); ?>
        <p class="text-xs font-semibold uppercase tracking-[0.4em] text-sky-500">Новости фабрики</p>
        <div class="mt-4 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl space-y-4">
                <h1 class="text-4xl font-bold text-slate-900 md:text-5xl">Пульс производства и инноваций</h1>
                <p class="text-lg text-slate-600 md:text-xl">Подборка свежих апдейтов о новых продуктах, модернизации линий и партнёрских проектах Salfatex. Следите за тем, что формирует рынок бумажной продукции уже сегодня.</p>
            </div>
            <div class="flex flex-wrap gap-4 text-sm text-slate-500">
                <div class="rounded-2xl border border-white/60 bg-white px-5 py-4 shadow-xl shadow-sky-100">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Всего материалов</p>
                    <p class="text-3xl font-semibold text-slate-900"><?php echo number_format($totalNews, 0, '.', ' '); ?></p>
                </div>
                <a class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-white px-5 py-4 font-semibold text-sky-600 shadow-lg shadow-sky-100 transition hover:border-sky-300" href="contact.php">
                    Связаться с нами
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 10h10.586l-3.293 3.293a1 1 0 101.414 1.414l5.006-5.006a1 1 0 000-1.414L12.707 3.28a1 1 0 10-1.414 1.414L14.586 8H4a1 1 0 100 2z"/></svg>
                </a>
            </div>
        </div>

        <?php if ($featured): ?>
            <?php $featuredImage = news_image_src($featured['image']); ?>
            <div class="mt-12 grid gap-8 md:grid-cols-[1.1fr_0.9fr]">
                <article class="group relative overflow-hidden rounded-3xl border border-white/70 bg-slate-900 text-white shadow-2xl">
                    <?php if ($featuredImage): ?>
                        <div class="absolute inset-0">
                            <?php echo render_picture($featuredImage, $featured['image_alt'] ?: $featured['title'], ['class' => 'h-full w-full object-cover opacity-60 transition duration-500 group-hover:scale-105']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="relative flex h-full flex-col justify-between bg-gradient-to-t from-slate-900/80 via-slate-900/60 to-transparent p-8">
                        <div class="space-y-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1 text-xs font-semibold tracking-wide">
                                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                                Актуально сейчас
                            </span>
                            <h2 class="text-3xl font-semibold leading-tight text-white"><?php echo h($featured['title']); ?></h2>
                            <p class="text-lg text-slate-200 md:text-xl"><?php echo h(mb_substr(strip_tags($featured['short_text']), 0, 200)); ?>...</p>
                        </div>
                        <div class="mt-6 flex items-center justify-between text-sm text-slate-200">
                            <span><?php echo date('d.m.Y', strtotime($featured['created_at'])); ?></span>
                            <a class="inline-flex items-center gap-2 rounded-full bg-white/10 px-5 py-2 font-semibold text-white transition hover:bg-white/20" href="news_item.php?slug=<?php echo h($featured['slug']); ?>">
                                Читать новость
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 10h10.586l-3.293 3.293a1 1 0 101.414 1.414l5.006-5.006a1 1 0 000-1.414L12.707 3.28a1 1 0 10-1.414 1.414L14.586 8H4a1 1 0 100 2z"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
                <div class="rounded-3xl border border-slate-200 bg-white/90 p-8 shadow-2xl shadow-sky-100">
                    <h3 class="text-2xl font-semibold text-slate-900">Что внутри подборки</h3>
                    <p class="mt-4 text-base text-slate-600">Каждый материал проходит редактуру и снабжается реальными цифрами производства. Мы делимся инсайтами по модернизации и показываем примеры внедрения новых решений.</p>
                    <ul class="mt-6 space-y-4 text-slate-700">
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-sky-600">1</span><div><p class="font-semibold">Новые продукты</p><p class="text-sm text-slate-500">Презентации линеек салфеток, полотенец и HoReCa-решений.</p></div></li>
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-sky-600">2</span><div><p class="font-semibold">Производство</p><p class="text-sm text-slate-500">Результаты оптимизации и обновления фабричных линий.</p></div></li>
                        <li class="flex items-start gap-3"><span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-sky-600">3</span><div><p class="font-semibold">Партнёрства</p><p class="text-sm text-slate-500">Истории сотрудничества с ретейлом и корпоративными клиентами.</p></div></li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-6">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Лента новостей</p>
                <h2 class="mt-2 text-3xl font-semibold text-slate-900">Свежие обновления</h2>
            </div>
            <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2">Аналитика</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2">Производство</span>
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2">HoReCa</span>
            </div>
        </div>

        <?php if ($news): ?>
            <div class="mt-10 grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($otherNews as $item): ?>
                    <?php $imageSrc = news_image_src($item['image']); ?>
                    <article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-[0_25px_60px_rgba(15,23,42,0.06)]">
                        <?php if ($imageSrc): ?>
                            <?php echo render_picture($imageSrc, $item['image_alt'] ?: $item['title'], ['class' => 'h-56 w-full object-cover']); ?>
                        <?php else: ?>
                            <div class="h-56 w-full bg-gradient-to-br from-slate-100 to-slate-200"></div>
                        <?php endif; ?>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">
                                <span><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></span>
                            </div>
                            <h3 class="mt-4 text-xl font-semibold text-slate-900"><?php echo h($item['title']); ?></h3>
                            <p class="mt-3 flex-1 text-sm text-slate-600"><?php echo h(mb_substr(strip_tags($item['short_text']), 0, 160)); ?>...</p>
                            <a class="mt-6 inline-flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-sky-200 hover:text-sky-600" href="news_item.php?slug=<?php echo h($item['slug']); ?>">
                                Подробнее
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M4 10h10.586l-3.293 3.293a1 1 0 101.414 1.414l5.006-5.006a1 1 0 000-1.414L12.707 3.28a1 1 0 10-1.414 1.414L14.586 8H4a1 1 0 100 2z"/></svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="mt-12 rounded-3xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                <p class="text-lg font-semibold text-slate-600">Пока нет новостей, но команда готовит свежие материалы.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php site_footer(); ?>
