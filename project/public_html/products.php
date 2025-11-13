<?php
require_once __DIR__ . '/../inc/helpers.php';
$categories = allowed_categories();
site_header('Каталог продукции');
?>
<section class="categories page">
    <div class="container">
        <h1>Категории продукции</h1>
        <p>Выберите нужное направление, чтобы ознакомиться с ассортиментом.</p>
        <div class="grid">
            <?php foreach ($categories as $key => $label): ?>
                <div class="category-card">
                    <h3><?php echo h($label); ?></h3>
                    <p>Гибкая настройка плотности, слоистости и рисунка тиснения.</p>
                    <a class="btn-secondary" href="category.php?category=<?php echo h($key); ?>">Перейти</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php site_footer(); ?>
