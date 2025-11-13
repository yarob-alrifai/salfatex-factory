<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = allowed_categories();
$latestNews = get_latest_news($pdo, 3);
$contact = get_contact_info($pdo);
site_header('Фабрика бумажной продукции');
?>
<section class="hero">
    <div class="container">
        <div class="hero-text">
            <h1>Современное производство бумажной продукции</h1>
            <p>Мы выпускаем салфетки, бумажные полотенца, туалетную бумагу и косметические салфетки для российских сетей и оптовых клиентов.</p>
            <a class="btn" href="products.php">Каталог продукции</a>
        </div>
        <div class="hero-banner">
            <img src="images/banner.jpg" alt="Производство бумаги" />
        </div>
    </div>
</section>
<section class="categories">
    <div class="container">
        <h2>Основные направления</h2>
        <div class="grid">
            <?php foreach ($categories as $key => $label): ?>
                <a class="category-card" href="category.php?category=<?php echo h($key); ?>">
                    <h3><?php echo h($label); ?></h3>
                    <p>Стабильное качество и гибкие варианты упаковки.</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="about">
    <div class="container">
        <h2>О фабрике</h2>
        <p>Фабрика расположена в Центральном федеральном округе и оснащена итальянскими и немецкими линиями полного цикла. Мы контролируем сырье, производим собственные втулки и гарантируем стерильность упаковки.</p>
        <p>Партнерам доступны услуги разработки частной марки и изготовление продукции под требования торговых сетей.</p>
    </div>
</section>
<section class="technology">
    <div class="container split">
        <div>
            <h2>Технологии производства</h2>
            <ul>
                <li>Автоматическая резка и укладка полотна.</li>
                <li>Система контроля влажности и плотности.</li>
                <li>Онлайн-мониторинг качества на всех этапах.</li>
                <li>Экологичная переработка отходов.</li>
            </ul>
        </div>
        <div>
            <h2>Оптовые поставки</h2>
            <p>Работаем по всей России, предоставляем логистическую поддержку и маркировку Честный ЗНАК. Индивидуальные условия для дистрибьюторов и сегмента HoReCa.</p>
            <?php if ($contact): ?>
                <a class="btn" href="tel:<?php echo h($contact['phone_main']); ?>">Позвонить отделу продаж</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="seo-text">
    <div class="container">
        <h2>Фабрика полного цикла</h2>
        <p>Наши салфетки и полотенца изготовлены из первичной целлюлозы и макулатуры высшего сорта. Мы внедряем бережливое производство, уделяем внимание дизайну упаковки и постоянно расширяем ассортимент. Благодаря автоматизации фабрика обеспечивает стабильный выпуск продукции даже при пиковых нагрузках.</p>
    </div>
</section>
<section class="gallery">
    <div class="container">
        <h2>Оборудование и производство</h2>
        <div class="gallery-track" data-gallery>
            <img src="images/machine1.jpg" alt="Линия резки" />
            <img src="images/machine2.jpg" alt="Линия упаковки" />
            <img src="images/machine3.jpg" alt="Склад" />
            <img src="images/machine4.jpg" alt="Контроль качества" />
        </div>
    </div>
</section>
<section class="news">
    <div class="container">
        <h2>Последние новости</h2>
        <div class="news-grid">
            <?php if ($latestNews): ?>
                <?php foreach ($latestNews as $item): ?>
                    <article>
                        <img src="uploads/<?php echo h($item['image']); ?>" alt="<?php echo h($item['title']); ?>">
                        <h3><?php echo h($item['title']); ?></h3>
                        <p><?php echo h(mb_substr(strip_tags($item['short_text']), 0, 120)); ?>...</p>
                        <a class="btn-secondary" href="news_item.php?id=<?php echo (int)$item['id']; ?>">Подробнее</a>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Новости появятся в ближайшее время.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
