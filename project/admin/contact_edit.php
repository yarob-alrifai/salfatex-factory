<?php
require_once __DIR__ . '/_layout.php';
require_admin();
$contact = get_contact_info($pdo);
admin_header('Contacts');
?>
<form method="post" action="contact_save.php">
    <label>Phone main<input type="text" name="phone_main" value="<?php echo h($contact['phone_main'] ?? ''); ?>" required></label>
    <label>Phone secondary<input type="text" name="phone_secondary" value="<?php echo h($contact['phone_secondary'] ?? ''); ?>"></label>
    <label>Email<input type="email" name="email" value="<?php echo h($contact['email'] ?? ''); ?>" required></label>
    <label>WhatsApp link<input type="text" name="whatsapp_link" value="<?php echo h($contact['whatsapp_link'] ?? ''); ?>"></label>
    <label>Telegram link<input type="text" name="telegram_link" value="<?php echo h($contact['telegram_link'] ?? ''); ?>"></label>
    <label>Address<textarea name="address" rows="3"><?php echo h($contact['address'] ?? ''); ?></textarea></label>
    <label>Map embed<textarea name="map_embed" rows="4"><?php echo h($contact['map_embed'] ?? ''); ?></textarea></label>
    <button class="btn" type="submit">Save</button>
</form>
<?php admin_footer(); ?>
