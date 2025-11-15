<?php
require_once __DIR__ . '/../inc/helpers.php';
$slugParam = $_GET['slug'] ?? '';
$news = $slugParam ? get_news_by_slug($slugParam) : null;
if (!$news) {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $news = $stmt->fetch();
    }
}
if (!$news) {
    http_response_code(404);
    echo 'Новость не найдена';
    exit;
}
$canonical = $news['canonical_url'] ?: site_url('news_item.php?slug=' . $news['slug']);
$plainText = trim(strip_tags($news['full_text'] ?: $news['short_text'] ?: $news['title']));
$wordCount = 0;
if ($plainText !== '') {
    preg_match_all('/[\p{L}\p{N}\']+/u', $plainText, $matches);
    $wordCount = count($matches[0]);
}
$readingMinutes = max(1, (int)ceil($wordCount / 180));
$meta = [
    'title' => $news['meta_title'] ?: $news['title'],
    'description' => $news['meta_description'] ?: mb_substr(strip_tags($news['short_text']), 0, 150),
    'keywords' => $news['meta_keywords'] ?: 'фабрика, бумажная продукция',
    'canonical' => $canonical,
    'og_title' => $news['og_title'] ?: $news['title'],
    'og_description' => $news['og_description'] ?: mb_substr(strip_tags($news['short_text']), 0, 150),
    'og_image' => $news['og_image'] ?: $news['image'],
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $news['title'],
            'datePublished' => $news['created_at'],
            'dateModified' => $news['created_at'],
            'image' => $news['og_image'] ?: $news['image'],
            'author' => ['@type' => 'Organization', 'name' => 'Salfatex Factory'],
            'mainEntityOfPage' => $canonical
        ]
    ]
];
site_header($news['title'], $meta);
$h1 = $news['h1'] ?: $news['title'];
$createdAt = new DateTime($news['created_at']);
$imageSrc = news_image_src($news['image']);
$shareUrl = rawurlencode($canonical);
$shareText = rawurlencode($news['title']);
$shareLinks = [
    'telegram' => 'https://t.me/share/url?url=' . $shareUrl . '&text=' . $shareText,
    'whatsapp' => 'https://api.whatsapp.com/send?text=' . $shareText . '%20' . $shareUrl,
];
$moreNewsRaw = get_latest_news($pdo, 4);
$moreNews = array_values(array_filter($moreNewsRaw, function ($item) use ($news) {
    return (int)$item['id'] !== (int)$news['id'];
}));
$moreNews = array_slice($moreNews, 0, 3);
$breadcrumbs = [
    ['label' => 'Главная', 'href' => site_url('index.php'), 'icon' => 'home'],
    ['label' => 'Новости', 'href' => site_url('news.php')],
    ['label' => $news['title'], 'current' => true],
];
?>
<div class="pointer-events-none fixed inset-x-0 top-0 z-[60] hidden md:block">
    <div class="h-1 bg-transparent">
        <div id="reading-progress" class="h-full w-0 bg-brand"></div>
    </div>
</div>
<section class="relative isolate overflow-hidden bg-slate-900 text-white">
    <div class="absolute inset-0 -z-10 opacity-70">
        <div class="absolute left-1/2 top-12 h-64 w-64 -translate-x-1/2 rounded-full bg-cyan-500 blur-[120px]"></div>
        <div class="absolute right-12 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-indigo-500 blur-[160px]"></div>
    </div>
    <div class="mx-auto max-w-6xl px-6 py-16">
        <?php echo render_breadcrumbs($breadcrumbs, ['class' => 'text-white/80']); ?>
        <div class="mb-6 flex items-center gap-3 text-sm text-white/70">
            <a class="flex items-center gap-2 font-semibold text-white/80 transition hover:text-white" href="news.php">
                <span aria-hidden="true">←</span>
                Все новости
            </a>
            <span class="h-1 w-1 rounded-full bg-white/40"></span>
            <time datetime="<?php echo $createdAt->format('Y-m-d'); ?>"><?php echo $createdAt->format('d.m.Y'); ?></time>
            <span class="h-1 w-1 rounded-full bg-white/40"></span>
            <span><?php echo $readingMinutes; ?> мин чтения</span>
        </div>
        <h1 class="max-w-4xl text-3xl font-semibold leading-tight tracking-tight text-white md:text-5xl">
            <?php echo h($h1); ?>
        </h1>
        <?php if ($news['short_text']): ?>
            <p class="mt-6 max-w-3xl text-lg text-white/80">
                <?php echo h(strip_tags($news['short_text'])); ?>
            </p>
        <?php endif; ?>
        <?php if ($imageSrc): ?>
            <div class="mt-10 overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-2 shadow-2xl">
                <?php echo render_picture($imageSrc, $news['image_alt'] ?: $news['title'], ['class' => 'h-[420px] w-full rounded-2xl object-cover']); ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<section class="relative -mt-16 pb-16 pt-4">
    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
        <article class="rounded-3xl border border-slate-100 bg-white/90 p-8 shadow-xl shadow-slate-200/60 backdrop-blur">
            <div class="prose prose-lg max-w-none text-slate-800 prose-h2:text-slate-900 prose-a:text-brand">
                <?php echo safe_html($news['full_text']); ?>
            </div>
        </article>
        <aside class="flex flex-col gap-6">
            <div class="rounded-3xl border border-slate-100 bg-white/90 p-6 shadow-lg shadow-slate-200/70 backdrop-blur">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">О публикации</p>
                <dl class="mt-4 space-y-3 text-sm text-slate-600">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <dt>Дата выпуска</dt>
                        <dd class="font-medium text-slate-900"><?php echo $createdAt->format('d.m.Y'); ?></dd>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <dt>Время чтения</dt>
                        <dd class="font-medium text-slate-900"><?php echo $readingMinutes; ?> мин</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>Объем</dt>
                        <dd class="font-medium text-slate-900"><?php echo number_format($wordCount, 0, '.', ' '); ?> слов</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-slate-900 p-6 text-white shadow-lg">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-white/60">Поделиться</p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/20" href="<?php echo h($shareLinks['telegram']); ?>" target="_blank" rel="noopener">
                        <span>Telegram</span>
                    </a>
                
                    <a class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/20" href="<?php echo h($shareLinks['whatsapp']); ?>" target="_blank" rel="noopener">
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
            <div class="rounded-3xl border border-dashed border-slate-200 bg-white/70 p-6 shadow-inner shadow-slate-200/80">
                <p class="text-base font-semibold text-slate-900">Хотите обсудить проект?</p>
                <p class="mt-2 text-sm text-slate-600">Свяжитесь с нами, чтобы получить коммерческое предложение и образцы продукции.</p>
                <a class="mt-4 inline-flex items-center justify-center rounded-2xl bg-brand px-4 py-2 text-sm font-semibold text-white shadow-glow transition hover:bg-brand-dark" href="contact.php">Связаться</a>
            </div>
        </aside>
    </div>
</section>
<?php if ($moreNews): ?>
    <section class="bg-slate-900/5 py-16">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-col gap-3 pb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-500">Ещё по теме</p>
                <h2 class="text-2xl font-semibold text-slate-900">Другие новости фабрики</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <?php foreach ($moreNews as $item): ?>
                    <article class="group relative flex h-full flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 transition hover:-translate-y-1 hover:shadow-2xl">
                        <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                            <span>Событие</span>
                            <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                            <time datetime="<?php echo h(date('Y-m-d', strtotime($item['created_at']))); ?>"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></time>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900">
                            <a class="transition group-hover:text-brand" href="news_item.php?slug=<?php echo h($item['slug']); ?>">
                                <?php echo h($item['title']); ?>
                            </a>
                        </h3>
                        <p class="mt-3 line-clamp-3 text-sm text-slate-600"><?php echo h(strip_tags($item['short_text'] ?: $item['full_text'])); ?></p>
                        <div class="mt-auto pt-6">
                            <a class="inline-flex items-center text-sm font-semibold text-brand transition group-hover:gap-2" href="news_item.php?slug=<?php echo h($item['slug']); ?>">
                                Читать полностью
                                <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<script>
(function () {
    var progress = document.getElementById('reading-progress');
    if (!progress) {
        return;
    }
    var article = document.querySelector('article');
    if (!article) {
        return;
    }
    function updateProgress() {
        var rect = article.getBoundingClientRect();
        var windowHeight = window.innerHeight || document.documentElement.clientHeight;
        var total = article.offsetHeight - windowHeight;
        if (total <= 0) {
            progress.style.width = '100%';
            return;
        }
        var scrolled = window.scrollY - (article.offsetTop - 120);
        var percent = Math.min(100, Math.max(0, (scrolled / total) * 100));
        progress.style.width = percent + '%';
    }
    document.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
    updateProgress();
})();
</script>
<?php site_footer(); ?>
