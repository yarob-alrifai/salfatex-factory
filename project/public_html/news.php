<?php
require_once __DIR__ . '/../inc/helpers.php';
$stmt = $pdo->query('SELECT * FROM news ORDER BY created_at DESC');
$news = $stmt->fetchAll();
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
                            <?php echo render_picture($imageSrc, $item['image_alt'] ?: $item['title'], ['class' => 'h-48 w-full rounded-2xl object-cover']); ?>
                        <?php endif; ?>
                        <h2><?php echo h($item['title']); ?></h2>
                        <p><?php echo h(mb_substr(strip_tags($item['short_text']), 0, 180)); ?>...</p>
                        <span class="date"><?php echo date('d.m.Y', strtotime($item['created_at'])); ?></span>
                        <a class="btn-secondary" href="news_item.php?slug=<?php echo h($item['slug']); ?>">Подробнее</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Пока нет новостей.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
