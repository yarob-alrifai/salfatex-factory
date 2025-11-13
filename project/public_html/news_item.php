<?php
require_once __DIR__ . '/../inc/helpers.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    echo 'Новость не найдена';
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
$stmt->execute(['id' => $id]);
$news = $stmt->fetch();
if (!$news) {
    http_response_code(404);
    echo 'Новость не найдена';
    exit;
}
$meta = [
    'title' => $news['meta_title'] ?: $news['title'],
    'description' => $news['meta_description'] ?: mb_substr(strip_tags($news['short_text']), 0, 150),
    'keywords' => $news['meta_keywords'] ?: 'фабрика, бумажная продукция'
];
site_header($news['title'], $meta);
?>
<section class="news-item">
    <div class="container">
        <a class="back-link" href="news.php">&larr; Все новости</a>
        <h1><?php echo h($news['title']); ?></h1>
        <span class="date"><?php echo date('d.m.Y', strtotime($news['created_at'])); ?></span>
        <?php $imageSrc = news_image_src($news['image']); ?>
        <?php if ($imageSrc): ?>
            <img class="hero-image" src="<?php echo h($imageSrc); ?>" alt="<?php echo h($news['title']); ?>">
        <?php endif; ?>
        <div class="full-text">
            <?php echo $news['full_text']; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
