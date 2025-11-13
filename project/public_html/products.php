<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = get_all_categories();
site_header('Каталог продукции');
?>
<section class="categories page">
    <div class="container">
        <h1>Категории продукции</h1>
        <p>Выберите нужное направление, чтобы ознакомиться с ассортиментом.</p>
        <?php if ($categories): ?>
            <div class="grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-card__media">
                            <?php if (!empty($category['hero_image'])): ?>
                                <img src="uploads/categories/<?php echo h($category['hero_image']); ?>" alt="<?php echo h($category['name']); ?>">
                            <?php else: ?>
                                <img src="images/placeholder.svg" alt="<?php echo h($category['name']); ?>">
                            <?php endif; ?>
                        </div>
                        <h3><?php echo h($category['name']); ?></h3>
                        <?php if (!empty($category['description'])): ?>
                            <p><?php echo h(mb_substr(strip_tags($category['description']), 0, 150)); ?>...</p>
                        <?php else: ?>
                            <p>Гибкая настройка плотности, слоистости и рисунка тиснения.</p>
                        <?php endif; ?>
                        <a class="btn-secondary" href="category.php?category=<?php echo h($category['slug']); ?>">Перейти</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Категории еще не добавлены.</p>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
