<?php
require_once __DIR__ . '/_layout.php';
require_admin();
admin_header('Add news');
?>
<form method="post" action="news_save.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('news_form'); ?>
    <label>Title<input type="text" name="title" required></label>
    <label>Slug<input type="text" name="slug" placeholder="factory-updates" required></label>
    <label>H1<input type="text" name="h1" placeholder="Заголовок на странице"></label>
    <label>Short text<textarea name="short_text" rows="3" data-rich-text required></textarea></label>
    <label>Full text<textarea name="full_text" rows="8" data-rich-text required></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Image
            <input type="file" name="image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">قص صورة الخبر بحيث تكون مربعة قبل الحفظ.</p>
    </div>
    <label>Image alt text<input type="text" name="image_alt" placeholder="Описание изображения"></label>
    <div class="form-field" data-crop-group>
        <label>OG image (optional)
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Если не загрузить, будет использовано основное изображение.</p>
    </div>
    <label>Meta title<input type="text" name="meta_title"></label>
    <label>Meta description<textarea name="meta_description" rows="2"></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords"></label>
    <label>OG title<input type="text" name="og_title"></label>
    <label>OG description<textarea name="og_description" rows="2"></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" placeholder="https://example.com/news/..."></label>
    <button class="btn" type="submit">Save</button>
</form>
<?php admin_footer(); ?>
