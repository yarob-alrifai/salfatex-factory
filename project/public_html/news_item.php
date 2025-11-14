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
?>
<section class="news-item">
    <div class="container">
        <a class="back-link" href="news.php">&larr; Все новости</a>
        <h1><?php echo h($h1); ?></h1>
        <span class="date"><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></span>
        <?php $imageSrc = news_image_src($news['image']); ?>
        <?php if ($imageSrc): ?>
            <?php echo render_picture($imageSrc, $news['image_alt'] ?: $news['title'], ['class' => 'hero-image']); ?>
        <?php endif; ?>
        <div class="full-text">
            <?php echo safe_html($news['full_text']); ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
