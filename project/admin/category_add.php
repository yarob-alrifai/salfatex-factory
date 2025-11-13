<?php
require_once __DIR__ . '/_layout.php';
require_admin();
admin_header('Add category');
?>
<form method="post" action="category_save.php" enctype="multipart/form-data">
    <label>Name<input type="text" name="name" required></label>
    <label>Slug<input type="text" name="slug" placeholder="napkins" required></label>
    <label>Description<textarea name="description" rows="4"></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Cover image
            <input type="file" name="hero_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">اختر الجزء الذي تريد إظهاره من الصورة قبل رفعها.</p>
    </div>
    <div class="form-field" data-crop-group>
        <label>Gallery images
            <input type="file" name="gallery[]" multiple accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">سيتم فتح أداة القص لكل صورة تحددها.</p>
    </div>
    <button class="btn" type="submit">Save category</button>
</form>
<?php admin_footer(); ?>
