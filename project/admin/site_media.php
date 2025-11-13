<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$heroImage = get_site_image('hero_banner');
$galleryImages = get_site_images('production_gallery');
admin_header('Media library');
?>
<div class="space-y-10">
    <div class="rounded-3xl border border-slate-800 bg-slate-950/40 p-6 shadow-2xl shadow-slate-900/40">
        <h2 class="text-2xl font-semibold text-white">Hero banner</h2>
        <p class="text-slate-400">Загрузите главное изображение, которое отображается на главной странице.</p>
        <?php if ($heroImage): ?>
            <div class="mt-6 flex flex-col gap-2">
                <img src="<?php echo h($heroImage['image_data']); ?>" alt="<?php echo h($heroImage['alt_text'] ?: 'Hero banner'); ?>" class="max-w-xs rounded-2xl border border-slate-800">
                <p class="text-sm text-slate-400">ALT: <?php echo h($heroImage['alt_text'] ?: 'не задан'); ?></p>
                <p class="text-sm text-slate-500">Последнее обновление: <?php echo h($heroImage['created_at']); ?></p>
                <p><a class="text-rose-300 hover:text-rose-200" href="site_media_delete.php?id=<?php echo (int)$heroImage['id']; ?>&asset=hero_banner" onclick="return confirm('Удалить изображение?');">Удалить изображение</a></p>
            </div>
        <?php endif; ?>
        <form class="mt-6 space-y-4" method="post" action="site_media_save.php" enctype="multipart/form-data">
        <input type="hidden" name="asset_key" value="hero_banner">
        <div class="form-field" data-crop-group>
            <label>Выберите изображение
                <input type="file" name="image" accept="image/*" data-crop-field required>
            </label>
            <p class="form-hint">Файл будет сохранён в базе данных.</p>
        </div>
        <label class="form-field">Альтернативный текст
            <input type="text" name="alt_text" value="<?php echo h($heroImage['alt_text'] ?? ''); ?>" placeholder="Например: Производственная линия">
        </label>
        <button class="btn" type="submit">Сохранить</button>
        </form>
    </div>
    <div class="rounded-3xl border border-slate-800 bg-slate-950/40 p-6 shadow-2xl shadow-slate-900/40">
        <h2 class="text-2xl font-semibold text-white">Галерея производства</h2>
        <p class="text-slate-400">Все изображения из этого раздела используются в публичной галерее.</p>
        <form class="mt-6 space-y-4" method="post" action="site_gallery_add.php" enctype="multipart/form-data">
            <div class="form-field" data-crop-group>
                <label>Добавить изображения
                    <input type="file" name="gallery_images[]" multiple accept="image/*" data-crop-field required>
                </label>
                <p class="form-hint">Можно выбрать несколько файлов сразу. Каждый файл будет сохранён отдельно.</p>
            </div>
            <label class="form-field">Альтернативный текст для новых изображений
                <input type="text" name="gallery_alt" placeholder="Производство" />
            </label>
            <button class="btn" type="submit">Загрузить</button>
        </form>
        <?php if ($galleryImages): ?>
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($galleryImages as $image): ?>
                    <figure class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4">
                        <img src="<?php echo h($image['image_data']); ?>" alt="<?php echo h($image['alt_text'] ?: 'Gallery image'); ?>" class="h-48 w-full rounded-xl object-cover">
                        <figcaption class="mt-3 flex items-center justify-between text-sm text-slate-300">
                            <span><?php echo h($image['alt_text'] ?: 'Без описания'); ?></span>
                            <a class="text-rose-300 hover:text-rose-200" href="site_gallery_delete.php?id=<?php echo (int)$image['id']; ?>" onclick="return confirm('Удалить это изображение?');">Удалить</a>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mt-4 text-slate-400">В галерее пока нет изображений.</p>
        <?php endif; ?>
    </div>
</div>
<?php admin_footer(); ?>
