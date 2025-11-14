<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$contact = get_contact_info($pdo);
admin_header('Contacts');
?>
<form method="post" action="contact_save.php" class="admin-form-grid">
    <?php echo csrf_field('contact_form'); ?>
    <label>Slug<input type="text" name="slug" value="<?php echo h($contact['slug'] ?? 'contact'); ?>" required></label>
    <label>H1<input type="text" name="h1" value="<?php echo h($contact['h1'] ?? ''); ?>" placeholder="Главный заголовок"></label>
    <label>Phone main<input type="text" name="phone_main" value="<?php echo h($contact['phone_main'] ?? ''); ?>" required></label>
    <label>Phone secondary<input type="text" name="phone_secondary" value="<?php echo h($contact['phone_secondary'] ?? ''); ?>"></label>
    <label>Email<input type="email" name="email" value="<?php echo h($contact['email'] ?? ''); ?>" required></label>
    <label>WhatsApp link<input type="text" name="whatsapp_link" value="<?php echo h($contact['whatsapp_link'] ?? ''); ?>"></label>
    <label>Telegram link<input type="text" name="telegram_link" value="<?php echo h($contact['telegram_link'] ?? ''); ?>"></label>
    <label>Address<textarea name="address" rows="3"><?php echo h($contact['address'] ?? ''); ?></textarea></label>
    <label>Map embed<textarea name="map_embed" rows="4"><?php echo h($contact['map_embed'] ?? ''); ?></textarea></label>
    <label>SEO text<textarea name="seo_text" rows="4"><?php echo h($contact['seo_text'] ?? ''); ?></textarea></label>
    <label>Meta title<input type="text" name="meta_title" value="<?php echo h($contact['meta_title'] ?? ''); ?>"></label>
    <label>Meta description<textarea name="meta_description" rows="2"><?php echo h($contact['meta_description'] ?? ''); ?></textarea></label>
    <label>Meta keywords<input type="text" name="meta_keywords" value="<?php echo h($contact['meta_keywords'] ?? ''); ?>"></label>
    <label>Canonical URL<input type="url" name="canonical_url" value="<?php echo h($contact['canonical_url'] ?? ''); ?>" placeholder="https://example.com/contact"></label>
    <button class="btn" type="submit">Save</button>
</form>
<?php admin_footer(); ?>
