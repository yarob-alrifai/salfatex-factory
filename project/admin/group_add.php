<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$categories = get_category_options();
admin_header('Add group');
?>
<form method="post" action="group_save.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('group_form'); ?>
    <label>Category
        <select name="category_id" required>
            <option value="">-- Select category --</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int)$category['id']; ?>"><?php echo h($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if (!$categories): ?>
        <p class="notice">Сначала создайте категорию.</p>
    <?php endif; ?>
    <label>Title<input type="text" name="group_title" required></label>
    <label>Slug<input type="text" name="slug" placeholder="premium-napkins" required></label>
    <label>H1<input type="text" name="h1" placeholder="Заголовок страницы"></label>
    <label>Left description<textarea name="left_description" rows="4" required></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="4"></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Main image
            <input type="file" name="main_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">اضبط القص المربع لصورة المنتج الرئيسية.</p>
    </div>
    <label>Main image alt<input type="text" name="main_image_alt" placeholder="Описание главного изображения"></label>
    <div class="form-field" data-crop-group>
        <label>OpenGraph image
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Можно загрузить отдельный баннер для соцсетей.</p>
    </div>
    <div class="form-field" data-crop-group>
        <label>Gallery images
            <input type="file" name="images[]" multiple data-crop-field>
        </label>
        <p class="form-hint">سيتم فتح أداة القص لكل صورة على حدة.</p>
    </div>
    <label>Default gallery alt<input type="text" name="gallery_default_alt" placeholder="Описание для новых изображений"></label>
    <label>Meta title<input type="text" name="meta_title"></label>
    <label>Meta description<textarea name="meta_description" rows="2"></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords"></label>
    <label>OG title<input type="text" name="og_title"></label>
    <label>OG description<textarea name="og_description" rows="2"></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" placeholder="https://example.com/group"></label>
    <div class="table-editor" data-table-editor>
        <h3>Dynamic table</h3>
        <div data-columns class="columns"></div>
        <button class="btn" type="button" data-add-column>Add column</button>
        <div data-rows class="rows"></div>
        <button class="btn" type="button" data-add-row>Add row</button>
    </div>
    <button class="btn" type="submit">Save group</button>
</form>
<?php admin_footer(); ?>
