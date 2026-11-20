<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();
$categories = get_category_options();
admin_header(admin_t('Add group'));
?>
<form method="post" action="group_save.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('group_form'); ?>
    <label><?php echo h(admin_t('Category')); ?>
        <select name="category_id" required>
            <option value=""><?php echo h(admin_t('-- Select category --')); ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int)$category['id']; ?>"><?php echo h($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <?php if (!$categories): ?>
        <p class="notice">Сначала создайте категорию.</p>
    <?php endif; ?>
    <label><?php echo h(admin_t('Title')); ?><input type="text" name="group_title" required></label>
    <label><?php echo h(admin_t('Slug')); ?><input type="text" name="slug" placeholder="premium-napkins" required></label>
    <label>H1<input type="text" name="h1" placeholder="Заголовок страницы"></label>
    <label><?php echo h(admin_t('Left description')); ?><textarea name="left_description" rows="4" required></textarea></label>
    <label><?php echo h(admin_t('SEO text')); ?><textarea name="seo_text" rows="4"></textarea></label>
    <div class="form-field" data-crop-group>
        <label><?php echo h(admin_t('Main image')); ?>
            <input type="file" name="main_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint"><?php echo h(admin_t('اضبط القص المربع لصورة المنتج الرئيسية.')); ?></p>
    </div>
    <label><?php echo h(admin_t('Main image alt')); ?><input type="text" name="main_image_alt" placeholder="<?php echo h(admin_t('Описание главного изображения')); ?>"></label>
    <div class="form-field" data-crop-group>
        <label><?php echo h(admin_t('OpenGraph image')); ?>
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Можно загрузить отдельный баннер для соцсетей.</p>
    </div>
    <div class="form-field" data-crop-group>
        <label><?php echo h(admin_t('Gallery images')); ?>
            <input type="file" name="images[]" multiple data-crop-field>
        </label>
        <p class="form-hint"><?php echo h(admin_t('سيتم فتح أداة القص لكل صورة على حدة.')); ?></p>
    </div>
    <label><?php echo h(admin_t('Default gallery alt')); ?><input type="text" name="gallery_default_alt" placeholder="Описание для новых изображений"></label>
    <label><?php echo h(admin_t('Meta title')); ?><input type="text" name="meta_title"></label>
    <label><?php echo h(admin_t('Meta description')); ?><textarea name="meta_description" rows="2"></textarea></label>
    <label><?php echo h(admin_t('Meta keywords')); ?><input type="text" name="meta_keywords"></label>
    <label><?php echo h(admin_t('OG title')); ?><input type="text" name="og_title"></label>
    <label><?php echo h(admin_t('OG description')); ?><textarea name="og_description" rows="2"></textarea></label>
    <label><?php echo h(admin_t('Canonical URL')); ?><input type="url" name="canonical_url" placeholder="https://example.com/group"></label>
    <div class="table-editor" data-table-editor>
        <h3><?php echo h(admin_t('Dynamic table')); ?></h3>
        <div data-columns class="columns"></div>
        <button class="btn" type="button" data-add-column><?php echo h(admin_t('Add column')); ?></button>
        <div data-rows class="rows"></div>
        <button class="btn" type="button" data-add-row><?php echo h(admin_t('Add row')); ?></button>
    </div>
    <button class="btn" type="submit"><?php echo h(admin_t('Save group')); ?></button>
</form>
<?php admin_footer(); ?>
