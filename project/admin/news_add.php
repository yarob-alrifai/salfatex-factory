<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
admin_header(admin_t('Add news'));
?>
<form method="post" action="news_save.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('news_form'); ?>
    <label><?php echo h(admin_t('Title')); ?><input type="text" name="title" required></label>
    <label><?php echo h(admin_t('Slug')); ?><input type="text" name="slug" placeholder="factory-updates" required></label>
    <label>H1<input type="text" name="h1" placeholder="Заголовок на странице"></label>
    <label><?php echo h(admin_t('Short text')); ?><textarea name="short_text" rows="3" data-rich-text required></textarea></label>
    <label><?php echo h(admin_t('Full text')); ?><textarea name="full_text" rows="8" data-rich-text required></textarea></label>
    <div class="form-field" data-crop-group>
        <label><?php echo h(admin_t('Image')); ?>
            <input type="file" name="image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint"><?php echo h(admin_t('قص صورة الخبر بحيث تكون مربعة قبل الحفظ.')); ?></p>
    </div>
    <label><?php echo h(admin_t('Image alt text')); ?><input type="text" name="image_alt" placeholder="<?php echo h(admin_t('Описание изображения')); ?>"></label>
    <div class="form-field" data-crop-group>
        <label><?php echo h(admin_t('OG image (optional)')); ?>
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Если не загрузить, будет использовано основное изображение.</p>
    </div>
    <label><?php echo h(admin_t('Meta title')); ?><input type="text" name="meta_title"></label>
    <label><?php echo h(admin_t('Meta description')); ?><textarea name="meta_description" rows="2"></textarea></label>
    <label><?php echo h(admin_t('Meta keywords')); ?><input type="text" name="meta_keywords"></label>
    <label><?php echo h(admin_t('OG title')); ?><input type="text" name="og_title"></label>
    <label><?php echo h(admin_t('OG description')); ?><textarea name="og_description" rows="2"></textarea></label>
    <label><?php echo h(admin_t('Canonical URL')); ?><input type="url" name="canonical_url" placeholder="https://example.com/news/..."></label>
    <button class="btn" type="submit"><?php echo h(admin_t('Save')); ?></button>
</form>
<?php admin_footer(); ?>
