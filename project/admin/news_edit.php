<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM news WHERE id = :id');
$stmt->execute(['id' => $id]);
$news = $stmt->fetch();
if (!$news) {
    header('Location: news_list.php');
    exit;
}
admin_header('Edit news');
?>
<form method="post" action="news_update.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('news_form'); ?>
    <input type="hidden" name="id" value="<?php echo (int)$news['id']; ?>">
    <label>Title<input type="text" name="title" value="<?php echo h($news['title']); ?>" required></label>
    <label>Slug<input type="text" name="slug" value="<?php echo h($news['slug']); ?>" required></label>
    <label>H1<input type="text" name="h1" value="<?php echo h($news['h1']); ?>"></label>
    <label>Short text<textarea name="short_text" rows="3" data-rich-text required><?php echo h($news['short_text']); ?></textarea></label>
    <label>Full text<textarea name="full_text" rows="8" data-rich-text required><?php echo h($news['full_text']); ?></textarea></label>
    <?php $currentImage = news_image_src($news['image']); ?>
    <?php if ($currentImage): ?>
        <div class="admin-preview">
            <p>Current image:</p>
            <img src="<?php echo h($currentImage); ?>" alt="<?php echo h($news['title']); ?>" style="max-width: 240px; height: auto; display: block; margin-bottom: 0.5rem;">
        </div>
    <?php endif; ?>
    <div class="form-field" data-crop-group>
        <label>Image
            <input type="file" name="image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">قص الصورة بعد اختيار ملف جديد لضمان توافق الأبعاد.</p>
    </div>
    <label>Image alt text<input type="text" name="image_alt" value="<?php echo h($news['image_alt']); ?>"></label>
    <div class="form-field" data-crop-group>
        <label>OG image
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Если оставить пустым, будет использоваться основное изображение.</p>
    </div>
    <?php if (!empty($news['og_image'])): ?>
        <div class="admin-preview">
            <p>Текущее OG изображение:</p>
            <img src="<?php echo h($news['og_image']); ?>" alt="<?php echo h($news['title']); ?>" style="max-width: 240px; height: auto;">
            <label class="form-checkbox"><input type="checkbox" name="remove_og_image" value="1">Удалить OG изображение</label>
        </div>
    <?php endif; ?>
    <label>Meta title<input type="text" name="meta_title" value="<?php echo h($news['meta_title']); ?>"></label>
    <label>Meta description<textarea name="meta_description" rows="2"><?php echo h($news['meta_description']); ?></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords" value="<?php echo h($news['meta_keywords']); ?>"></label>
    <label>OG title<input type="text" name="og_title" value="<?php echo h($news['og_title']); ?>"></label>
    <label>OG description<textarea name="og_description" rows="2"><?php echo h($news['og_description']); ?></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" value="<?php echo h($news['canonical_url']); ?>"></label>
    <button class="btn" type="submit">Update</button>
</form>
<?php admin_footer(); ?>
