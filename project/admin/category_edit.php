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
<form method="post" action="category_update.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo (int)$category['id']; ?>">
    <label>Name<input type="text" name="name" value="<?php echo h($category['name']); ?>" required></label>
    <label>Slug<input type="text" name="slug" value="<?php echo h($category['slug']); ?>" required></label>
    <label>Description<textarea name="description" rows="4"><?php echo h($category['description']); ?></textarea></label>
    <label>Cover image<input type="file" name="hero_image" accept="image/*"></label>
    <?php if ($category['hero_image']): ?>
        <div>
            <p>Current cover:</p>
            <img src="<?php echo h($category['hero_image']); ?>" alt="<?php echo h($category['name']); ?>" style="max-width: 240px; height: auto;">
            <p><a href="category_hero_delete.php?id=<?php echo (int)$category['id']; ?>" onclick="return confirm('Удалить обложку?');">Удалить обложку</a></p>
        </div>
    <?php endif; ?>
    <label>Gallery images<input type="file" name="gallery[]" multiple accept="image/*"></label>
    <?php if ($gallery): ?>
        <div>
            <p>Gallery:</p>
            <ul>
                <?php foreach ($gallery as $image): ?>
                    <li>
                        <img src="<?php echo h($image['image_path']); ?>" alt="<?php echo h($category['name']); ?>" style="max-width: 200px; height: auto; display: block; margin-bottom: 0.5rem;">
                        <a href="category_gallery_delete.php?id=<?php echo (int)$image['id']; ?>&category_id=<?php echo (int)$category['id']; ?>" onclick="return confirm('Удалить изображение?');">Удалить</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <button class="btn" type="submit">Update category</button>
</form>
<?php admin_footer(); ?>
