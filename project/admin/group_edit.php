<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM product_groups WHERE id = :id');
$stmt->execute(['id' => $id]);
$group = $stmt->fetch();
if (!$group) {
    header('Location: groups_list.php');
    exit;
}
$categories = get_category_options();
$columnStmt = $pdo->prepare('SELECT * FROM product_group_columns WHERE group_id = :id ORDER BY order_index');
$columnStmt->execute(['id' => $id]);
$columns = $columnStmt->fetchAll();
$rowStmt = $pdo->prepare('SELECT * FROM product_group_rows WHERE group_id = :id ORDER BY row_index');
$rowStmt->execute(['id' => $id]);
$rows = $rowStmt->fetchAll();
$cellsStmt = $pdo->prepare('SELECT c.id as column_id, r.id as row_id, cell.value FROM product_group_cells cell JOIN product_group_rows r ON cell.row_id = r.id JOIN product_group_columns c ON cell.column_id = c.id WHERE r.group_id = :id');
$cellsStmt->execute(['id' => $id]);
$cellMap = [];
foreach ($cellsStmt as $cell) {
    $cellMap[$cell['row_id']][$cell['column_id']] = $cell['value'];
}
$imageStmt = $pdo->prepare('SELECT * FROM product_group_images WHERE group_id = :id');
$imageStmt->execute(['id' => $id]);
$images = $imageStmt->fetchAll();
admin_header('Edit group');
?>
<form method="post" action="group_update.php" enctype="multipart/form-data" class="admin-form-grid">
    <?php echo csrf_field('group_form'); ?>
    <input type="hidden" name="id" value="<?php echo (int)$group['id']; ?>">
    <label>Category
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo (int)$category['id']; ?>" <?php echo ((int)$group['category_id'] === (int)$category['id']) ? 'selected' : ''; ?>><?php echo h($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Title<input type="text" name="group_title" value="<?php echo h($group['group_title']); ?>" required></label>
    <label>Slug<input type="text" name="slug" value="<?php echo h($group['slug']); ?>" required></label>
    <label>H1<input type="text" name="h1" value="<?php echo h($group['h1']); ?>"></label>
    <label>Left description<textarea name="left_description" rows="4" required><?php echo h($group['left_description']); ?></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="4"><?php echo h($group['seo_text']); ?></textarea></label>
    <div class="form-field" data-crop-group>
        <label>Main image
            <input type="file" name="main_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">قص مربع قبل استبدال الصورة الرئيسية.</p>
    </div>
    <label>Main image alt<input type="text" name="main_image_alt" value="<?php echo h($group['main_image_alt']); ?>"></label>
    <?php if (!empty($group['main_image'])): ?>
        <div class="admin-preview">
            <p>Current main image:</p>
            <img src="<?php echo h($group['main_image']); ?>" alt="<?php echo h($group['group_title']); ?>" style="max-width: 240px; height: auto;">
            <p><a href="group_main_image_delete.php?id=<?php echo (int)$group['id']; ?>" onclick="return confirm('Удалить главное изображение?');">Удалить главное изображение</a></p>
        </div>
    <?php endif; ?>
    <div class="form-field" data-crop-group>
        <label>OpenGraph image
            <input type="file" name="og_image" accept="image/*" data-crop-field>
        </label>
        <p class="form-hint">Соцсети используют это изображение. При отсутствии используется основное фото.</p>
    </div>
    <?php if (!empty($group['og_image'])): ?>
        <div class="admin-preview">
            <p>Текущее OG изображение:</p>
            <img src="<?php echo h($group['og_image']); ?>" alt="<?php echo h($group['group_title']); ?>" style="max-width: 240px; height: auto;">
            <label class="form-checkbox">
                <input type="checkbox" name="remove_og_image" value="1">Удалить OG изображение
            </label>
        </div>
    <?php endif; ?>
    <div class="form-field" data-crop-group>
        <label>Add images
            <input type="file" name="images[]" multiple data-crop-field>
        </label>
        <p class="form-hint">قم بتحديد موضع القص لكل صورة ترفعها.</p>
    </div>
    <label>Default gallery alt<input type="text" name="gallery_default_alt" placeholder="Описание для новых изображений"></label>
    <?php if ($images): ?>
        <div class="admin-gallery-grid">
            <?php foreach ($images as $image): ?>
                <div class="admin-gallery-card">
                    <img src="<?php echo h($image['image_path']); ?>" alt="<?php echo h($group['group_title']); ?>" style="max-width: 200px; height: auto; display: block; margin-bottom: 0.5rem;">
                    <label>Alt text
                        <input type="text" name="gallery_alt[<?php echo (int)$image['id']; ?>]" value="<?php echo h($image['alt_text']); ?>">
                    </label>
                    <a href="group_images_delete.php?id=<?php echo (int)$image['id']; ?>&group_id=<?php echo (int)$group['id']; ?>" onclick="return confirm('Delete image?');">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <label>Meta title<input type="text" name="meta_title" value="<?php echo h($group['meta_title']); ?>"></label>
    <label>Meta description<textarea name="meta_description" rows="2"><?php echo h($group['meta_description']); ?></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords" value="<?php echo h($group['meta_keywords']); ?>"></label>
    <label>OG title<input type="text" name="og_title" value="<?php echo h($group['og_title']); ?>"></label>
    <label>OG description<textarea name="og_description" rows="2"><?php echo h($group['og_description']); ?></textarea></label>
    <label>Canonical URL<input type="url" name="canonical_url" value="<?php echo h($group['canonical_url']); ?>"></label>
    <div class="table-editor" data-table-editor>
        <h3>Dynamic table</h3>
        <div data-columns class="columns">
            <?php foreach ($columns as $column): ?>
                <div class="column-input">
                    <input type="text" name="columns[]" value="<?php echo h($column['column_name']); ?>" required>
                    <button type="button">×</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn" type="button" data-add-column>Add column</button>
        <div data-rows class="rows">
            <?php foreach ($rows as $order => $row): ?>
                <?php
                $values = [];
                foreach ($columns as $idx => $column) {
                    $values[$idx] = $cellMap[$row['id']][$column['id']] ?? '';
                }
                ?>
                <div class="row-block" data-index="<?php echo $order; ?>" data-values='<?php echo h(json_encode($values)); ?>'>
                    <input type="hidden" name="rows[]" value="<?php echo $order; ?>">
                    <button type="button">Удалить строку</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="btn" type="button" data-add-row>Add row</button>
    </div>
    <button class="btn" type="submit">Update group</button>
</form>
<?php admin_footer(); ?>
