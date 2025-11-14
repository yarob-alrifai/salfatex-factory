<?php
require_once __DIR__ . '/_layout.php';
require_admin();
admin_header('Add category');
?>
<form method="post" action="category_save.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('category_form'); ?>
    <label>Name<input type="text" name="name" required></label>
    <label>Slug<input type="text" name="slug" placeholder="napkins" required></label>
    <label>H1<input type="text" name="h1" placeholder="Основной заголовок"></label>
    <label>Description<textarea name="description" rows="4"></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="5"></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Cover image
            <input type="file" name="hero_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">اختر الجزء الذي تريد إظهاره من الصورة قبل رفعها.</p>
    </div>
    <label>Cover alt text<input type="text" name="hero_image_alt" placeholder="Описание главного изображения"></label>
    <div class="form-field" data-crop-group>
        <label>OpenGraph image (optional)
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Если не загрузить отдельный файл, будет использована обложка.</p>
    </div>
    <div class="form-field" data-crop-group>
        <label>Gallery images
            <input type="file" name="gallery[]" multiple accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">سيتم فتح أداة القص لكل صورة تحددها.</p>
    </div>
    <label>Default alt for new gallery images<input type="text" name="gallery_default_alt" placeholder="Описание для новых изображений"></label>
    <label>Meta title<input type="text" name="meta_title"></label>
    <label>Meta description<textarea name="meta_description" rows="2"></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords"></label>
    <label>OG title<input type="text" name="og_title"></label>
    <label>OG description<textarea name="og_description" rows="2"></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" placeholder="https://example.com/category"></label>
    <button class="btn" type="submit">Save category</button>
</form>
<?php admin_footer(); ?>
