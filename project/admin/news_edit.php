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
<form method="post" action="news_update.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo (int)$news['id']; ?>">
    <label>Title<input type="text" name="title" value="<?php echo h($news['title']); ?>" required></label>
    <label>Short text<textarea name="short_text" rows="3" required><?php echo h($news['short_text']); ?></textarea></label>
    <label>Full text<textarea name="full_text" rows="8" required><?php echo h($news['full_text']); ?></textarea></label>
    <?php $currentImage = news_image_src($news['image']); ?>
    <?php if ($currentImage): ?>
        <div>
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
    <label>Meta title<input type="text" name="meta_title" value="<?php echo h($news['meta_title']); ?>"></label>
    <label>Meta description<textarea name="meta_description" rows="2"><?php echo h($news['meta_description']); ?></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords" value="<?php echo h($news['meta_keywords']); ?>"></label>
    <button class="btn" type="submit">Update</button>
</form>
<?php admin_footer(); ?>
