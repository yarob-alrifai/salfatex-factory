<?php
require_once __DIR__ . '/../inc/helpers.php';
$stmt = $pdo->query('SELECT * FROM news ORDER BY created_at DESC');
$news = $stmt->fetchAll();
site_header('Новости фабрики');
?>
<section class="news page">
    <div class="container">
        <h1>Новости</h1>
        <div class="news-grid">
            <?php if ($news): ?>
                <?php foreach ($news as $item): ?>
                    <?php $imageSrc = news_image_src($item['image']); ?>
                    <article>
                        <?php if ($imageSrc): ?>
                            <img src="<?php echo h($imageSrc); ?>" alt="<?php echo h($item['title']); ?>">
                        <?php endif; ?>
                        <h2><?php echo h($item['title']); ?></h2>
                        <p><?php echo h(mb_substr(strip_tags($item['short_text']), 0, 180)); ?>...</p>
                        <span class="date"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></span>
                        <a class="btn-secondary" href="news_item.php?id=<?php echo (int)$item['id']; ?>">Подробнее</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Пока нет новостей.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
