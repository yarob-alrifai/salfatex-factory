<?php
require_once __DIR__ . '/_layout.php';
require_admin();
admin_header('Add news');
?>
<form method="post" action="news_save.php" enctype="multipart/form-data">
    <label>Title<input type="text" name="title" required></label>
    <label>Short text<textarea name="short_text" rows="3" required></textarea></label>
    <label>Full text<textarea name="full_text" rows="8" required></textarea></label>
    <label>Image<input type="file" name="image"></label>
    <label>Meta title<input type="text" name="meta_title"></label>
    <label>Meta description<textarea name="meta_description" rows="2"></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords"></label>
    <button class="btn" type="submit">Save</button>
</form>
<?php admin_footer(); ?>
