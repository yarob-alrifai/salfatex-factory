<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM product_categories WHERE id = :id');
$stmt->execute(['id' => $id]);
$category = $stmt->fetch();
if (!$category) {
    header('Location: categories_list.php');
    exit;
}
$galleryStmt = $pdo->prepare('SELECT * FROM product_category_images WHERE category_id = :id');
$galleryStmt->execute(['id' => $id]);
$gallery = $galleryStmt->fetchAll();
admin_header('Edit category');
?>
<form method="post" action="category_update.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('category_form'); ?>
    <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
    <label>Name<input type="text" name="name" value="<?php echo h($category['name']); ?>" required></label>
    <label>Slug<input type="text" name="slug" value="<?php echo h($category['slug']); ?>" required></label>
    <label>H1<input type="text" name="h1" value="<?php echo h($category['h1']); ?>" placeholder="Основной заголовок"></label>
    <label>Description<textarea name="description" rows="4"><?php echo h($category['description']); ?></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="5"><?php echo h($category['seo_text']); ?></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Cover image
            <input type="file" name="hero_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">اختر الجزء الذي تريد إظهاره من الصورة قبل رفعها.</p>
    </div>
    <label>Cover alt text<input type="text" name="hero_image_alt" value="<?php echo h($category['hero_image_alt']); ?>"></label>
    <?php if ($category['hero_image']): ?>
        <div class="admin-preview">
            <p>Current cover:</p>
            <img src="<?php echo h($category['hero_image']); ?>" alt="<?php echo h($category['name']); ?>" style="max-width: 240px; height: auto;">
            <p><a href="category_hero_delete.php?id=<?php echo (int)$category['id']; ?>" onclick="return confirm('Удалить обложку?');">Удалить обложку</a></p>
        </div>
    <?php endif; ?>
    <div class="form-field" data-crop-group>
        <label>OpenGraph image
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Используется в соцсетях. Если не задано, возьмём обложку.</p>
    </div>
    <?php if (!empty($category['og_image'])): ?>
        <div class="admin-preview">
            <p>Текущее OG изображение:</p>
            <img src="<?php echo h($category['og_image']); ?>" alt="<?php echo h($category['name']); ?>" style="max-width: 240px; height: auto;">
            <label class="form-checkbox">
                <input type="checkbox" name="remove_og_image" value="1">Удалить OG изображение
            </label>
        </div>
    <?php endif; ?>
    <div class="form-field" data-crop-group>
        <label>Gallery images
            <input type="file" name="gallery[]" multiple accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">سيتم فتح أداة القص لكل صورة تحددها.</p>
    </div>
    <label>Default alt for new gallery images<input type="text" name="gallery_default_alt" placeholder="Описание для новых изображений"></label>
    <?php if ($gallery): ?>
        <div class="admin-gallery-grid">
            <?php foreach ($gallery as $image): ?>
                <div class="admin-gallery-card">
                    <img src="<?php echo h($image['image_path']); ?>" alt="<?php echo h($category['name']); ?>" style="max-width: 200px; height: auto; display: block; margin-bottom: 0.5rem;">
                    <label>Alt text
                        <input type="text" name="gallery_alt[<?php echo (int)$image['id']; ?>]" value="<?php echo h($image['alt_text']); ?>">
                    </label>
                    <a href="category_gallery_delete.php?id=<?php echo (int)$image['id']; ?>&category_id=<?php echo (int)$category['id']; ?>" onclick="return confirm('Удалить изображение?');">Удалить</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <label>Meta title<input type="text" name="meta_title" value="<?php echo h($category['meta_title']); ?>"></label>
    <label>Meta description<textarea name="meta_description" rows="2"><?php echo h($category['meta_description']); ?></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords" value="<?php echo h($category['meta_keywords']); ?>"></label>
    <label>OG title<input type="text" name="og_title" value="<?php echo h($category['og_title']); ?>"></label>
    <label>OG description<textarea name="og_description" rows="2"><?php echo h($category['og_description']); ?></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" value="<?php echo h($category['canonical_url']); ?>" placeholder="https://example.com/category"></label>
    <button class="btn" type="submit">Update category</button>
</form>
<?php admin_footer(); ?>
