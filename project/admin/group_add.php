<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$categories = allowed_categories();
admin_header('Add group');
?>
<form method="post" action="group_save.php" enctype="multipart/form-data">
    <label>Category
        <select name="category" required>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?php echo h($key); ?>"><?php echo h($label); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Title<input type="text" name="group_title" required></label>
    <label>Left description<textarea name="left_description" rows="4" required></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="4"></textarea></label>
    <label>Gallery images<input type="file" name="images[]" multiple></label>
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
