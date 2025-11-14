<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = get_all_categories();
$latestNews = get_latest_news($pdo, 3);
$contact = get_contact_info($pdo);
$heroBanner = get_site_image('hero_banner');
$productionGallery = get_site_images('production_gallery');
$heroSrc = site_image_src($heroBanner['image_data'] ?? null);
$orgSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Salfatex Factory',
    'url' => site_url('index.php'),
    'logo' => $heroSrc ?: site_url('images/logo.svg')
];
if ($contact) {
    $orgSchema['contactPoint'] = [
        '@type' => 'ContactPoint',
        'telephone' => $contact['phone_main'],
        'contactType' => 'sales'
    ];
}
$meta = [
    'title' => 'Фабрика бумажной продукции полного цикла «Салфатекс»',
    'description' => 'Производим салфетки, полотенца и туалетную бумагу с автоматическим контролем качества. Собственное оборудование и гибкие условия поставок.',
    'keywords' => 'салфетки оптом, бумажные полотенца, туалетная бумага, производство',
    'canonical' => site_url('index.php'),
    'og_image' => $heroSrc ?: '',
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Salfatex Factory',
            'url' => site_url('index.php'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => site_url('products.php') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string'
            ]
        ],
        $orgSchema
    ]
];
site_header('Фабрика бумажной продукции', $meta);
?>
<section class="hero relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-[600px] bg-gradient-to-br from-sky-100 via-white to-indigo-100"></div>
    <div class="mx-auto flex max-w-6xl flex-col-reverse items-center gap-12 px-6 py-20 lg:flex-row">
        <div class="hero-text max-w-2xl space-y-6">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-sky-600">Производство полного цикла</p>
            <h1 class="font-display text-4xl font-bold text-slate-900 md:text-5xl">Современное производство бумажной продукции</h1>
            <p class="text-lg text-slate-600">Мы выпускаем салфетки, бумажные полотенца, туалетную бумагу и косметические салфетки для российских сетей и оптовых клиентов. Контролируем каждый этап и гарантируем чистоту упаковки.</p>
            <div class="flex flex-wrap gap-4">
                <a class="btn inline-flex items-center justify-center rounded-full bg-brand px-6 py-3 text-base font-semibold text-white shadow-glow transition hover:-translate-y-0.5 hover:bg-brand-dark" href="products.php">Каталог продукции</a>
                <a class="inline-flex items-center justify-center rounded-full border border-slate-300 px-6 py-3 text-base font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-900" href="contact.php">Связаться с нами</a>
            </div>
            <div class="grid gap-6 pt-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-slate-900">120+</p>
                    <p class="text-sm text-slate-500">товарных позиций</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-slate-900">24/7</p>
                    <p class="text-sm text-slate-500">онлайн контроль качества</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-center shadow-sm">
                    <p class="text-3xl font-bold text-slate-900">15 лет</p>
                    <p class="text-sm text-slate-500">на рынке HoReCa</p>
                </div>
            </div>
        </div>
        <div class="hero-banner relative">
            <div class="absolute -inset-6 -z-10 rounded-[40px] bg-gradient-to-tr from-sky-200/80 via-white to-indigo-200 blur-3xl"></div>
            <?php if ($heroSrc): ?>
                <?php echo render_picture($heroSrc, $heroBanner['alt_text'] ?? 'Производство бумаги', ['class' => 'relative block rounded-[32px] shadow-2xl shadow-sky-200', 'loading' => 'eager', 'decoding' => 'sync']); ?>
            <?php else: ?>
                <div class="relative flex h-80 w-80 items-center justify-center rounded-[32px] border border-dashed border-slate-200 bg-white text-center text-slate-500">
                    <p class="px-6">Добавьте обложку в разделе «Медиа» панели администратора.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="categories bg-white py-16">
    <div class="mx-auto max-w-6xl space-y-10 px-6">
        <div class="flex flex-col gap-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Категории</p>
            <h2 class="text-3xl font-semibold text-slate-900">Основные направления</h2>
            <p class="text-slate-600">Выберите категорию, чтобы узнать о возможностях упаковки, плотности и вариантах дизайна.</p>
        </div>
        <?php if ($categories): ?>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($categories as $category): ?>
                    <a class="category-card group flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white/70 p-6 shadow-sm transition hover:-translate-y-1 hover:border-brand hover:shadow-glow" href="category.php?category=<?php echo h($category['slug']); ?>">
                        <div class="category-card__media overflow-hidden rounded-2xl">
                            <?php if (!empty($category['hero_image'])): ?>
                                <?php echo render_picture($category['hero_image'], $category['hero_image_alt'] ?: $category['name'], ['class' => 'h-52 w-full object-cover transition duration-500 group-hover:scale-105']); ?>
                            <?php else: ?>
                                <div class="flex h-52 w-full items-center justify-center bg-slate-100 text-sm text-slate-500">Нет изображения</div>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-xl font-semibold text-slate-900"><?php echo h($category['name']); ?></h3>
                            <?php if (!empty($category['description'])): ?>
                                <p class="text-sm text-slate-600"><?php echo h(mb_substr(strip_tags($category['description']), 0, 120)); ?>...</p>
                            <?php else: ?>
                                <p class="text-sm text-slate-600">Стабильное качество и гибкие варианты упаковки.</p>
                            <?php endif; ?>
                        </div>
                        <span class="inline-flex items-center text-sm font-semibold text-brand">Смотреть подробнее →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600">
                <p>Добавьте первую категорию, чтобы показать продукцию на главной странице.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<section class="about bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-16 text-white">
    <div class="mx-auto max-w-5xl space-y-6 px-6 text-lg">
        <h2 class="text-3xl font-semibold">О фабрике</h2>
        <p class="text-slate-200">Фабрика расположена в Центральном федеральном округе и оснащена итальянскими и немецкими линиями полного цикла. Мы контролируем сырье, производим собственные втулки и гарантируем стерильность упаковки.</p>
        <p class="text-slate-200">Партнерам доступны услуги разработки частной марки и изготовление продукции под требования торговых сетей.</p>
    </div>
</section>
<section class="technology bg-white py-16">
    <div class="mx-auto flex max-w-6xl flex-col gap-10 px-6 lg:flex-row">
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Технологии производства</h2>
            <ul class="mt-6 space-y-4 text-slate-600">
                <li class="flex items-start gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>Автоматическая резка и укладка полотна.</li>
                <li class="flex items-start gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>Система контроля влажности и плотности.</li>
                <li class="flex items-start gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>Онлайн-мониторинг качества на всех этапах.</li>
                <li class="flex items-start gap-3"><span class="mt-1 h-2 w-2 rounded-full bg-brand"></span>Экологичная переработка отходов.</li>
            </ul>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-sky-50 to-white p-8 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Оптовые поставки</h2>
            <p class="mt-6 text-slate-600">Работаем по всей России, предоставляем логистическую поддержку и маркировку Честный ЗНАК. Индивидуальные условия для дистрибьюторов и сегмента HoReCa.</p>
            <?php if ($contact): ?>
                <a class="mt-8 inline-flex items-center justify-center rounded-2xl bg-brand px-5 py-3 font-semibold text-white shadow-glow transition hover:-translate-y-0.5 hover:bg-brand-dark" href="tel:<?php echo h($contact['phone_main']); ?>">Позвонить отделу продаж</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="seo-text bg-slate-50 py-16">
    <div class="mx-auto max-w-4xl space-y-4 px-6">
        <h2 class="text-3xl font-semibold text-slate-900">Фабрика полного цикла</h2>
        <p class="text-lg text-slate-600">Наши салфетки и полотенца изготовлены из первичной целлюлозы и макулатуры высшего сорта. Мы внедряем бережливое производство, уделяем внимание дизайну упаковки и постоянно расширяем ассортимент. Благодаря автоматизации фабрика обеспечивает стабильный выпуск продукции даже при пиковых нагрузках.</p>
    </div>
</section>
<section class="gallery bg-white py-16">
    <div class="mx-auto max-w-6xl space-y-8 px-6">
        <div class="flex flex-col gap-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Производство</p>
            <h2 class="text-3xl font-semibold text-slate-900">Оборудование и производство</h2>
        </div>
        <?php if ($productionGallery): ?>
            <div class="gallery-slider" data-slider data-slider-interval="3000">
                <div class="gallery-slider__track" data-slider-track>
                    <?php foreach ($productionGallery as $galleryItem): ?>
                        <?php $gallerySrc = site_image_src($galleryItem['image_data']); ?>
                        <?php if ($gallerySrc): ?>
                            <div class="gallery-slide">
                                <?php echo render_picture($gallerySrc, $galleryItem['alt_text'] ?: 'Производство', ['class' => 'gallery-slide__image']); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="gallery-slider__dots" data-slider-dots></div>
            </div>
        <?php else: ?>
            <div class="flex h-64 w-full items-center justify-center rounded-3xl border border-dashed border-slate-200 bg-slate-50 text-slate-500">
                <p>Добавьте изображения производства через панель администратора.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<section class="news bg-slate-50 py-16">
    <div class="mx-auto max-w-6xl space-y-10 px-6">
        <div class="flex flex-col gap-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Новости</p>
            <h2 class="text-3xl font-semibold text-slate-900">Последние новости</h2>
        </div>
        <div class="news-grid grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <?php if ($latestNews): ?>
                <?php foreach ($latestNews as $item): ?>
                    <?php $imageSrc = news_image_src($item['image']); ?>
                    <article class="flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                        <?php if ($imageSrc): ?>
                            <?php echo render_picture($imageSrc, $item['image_alt'] ?: $item['title'], ['class' => 'h-48 w-full rounded-2xl object-cover']); ?>
                        <?php endif; ?>
                        <div class="mt-5 flex flex-1 flex-col">
                            <h3 class="text-xl font-semibold text-slate-900"><?php echo h($item['title']); ?></h3>
                            <p class="mt-3 flex-1 text-sm text-slate-600"><?php echo h(mb_substr(strip_tags($item['short_text']), 0, 120)); ?>...</p>
                            <a class="btn-secondary mt-5 inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2 font-semibold text-slate-700 transition hover:border-brand hover:text-brand" href="news_item.php?slug=<?php echo h($item['slug']); ?>">Подробнее</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-slate-600">Новости появятся в ближайшее время.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
