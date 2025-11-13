<?php
require_once __DIR__ . '/../inc/helpers.php';
$contact = get_contact_info($pdo);
site_header('Контакты фабрики');
?>
<section class="contact">
    <div class="container">
        <h1>Контакты</h1>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert">
                <?php echo h($_SESSION['flash']); unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        <?php if ($contact): ?>
            <div class="contact-info">
                <a class="btn" href="tel:<?php echo h($contact['phone_main']); ?>">Позвонить: <?php echo h($contact['phone_main']); ?></a>
                <?php if (!empty($contact['phone_secondary'])): ?>
                    <a class="btn-secondary" href="tel:<?php echo h($contact['phone_secondary']); ?>">Доп. телефон</a>
                <?php endif; ?>
                <a class="btn-secondary" href="mailto:<?php echo h($contact['email']); ?>">Email</a>
                <?php if (!empty($contact['whatsapp_link'])): ?>
                    <a class="btn" target="_blank" rel="noopener" href="<?php echo h($contact['whatsapp_link']); ?>">WhatsApp</a>
                <?php endif; ?>
                <?php if (!empty($contact['telegram_link'])): ?>
                    <a class="btn" target="_blank" rel="noopener" href="<?php echo h($contact['telegram_link']); ?>">Telegram</a>
                <?php endif; ?>
                <p><?php echo nl2br(h($contact['address'])); ?></p>
            </div>
            <div class="map">
                <?php echo $contact['map_embed']; ?>
            </div>
        <?php else: ?>
            <p>Контактная информация появится в ближайшее время.</p>
        <?php endif; ?>
        <div class="contact-form">
            <h2>Связаться с нами</h2>
            <form id="contactForm" method="post" action="send_message.php">
                <input type="text" name="name" placeholder="Ваше имя" required>
                <input type="text" name="phone" placeholder="Телефон" required>
                <input type="email" name="email" placeholder="Email" required>
                <textarea name="message" placeholder="Сообщение" required></textarea>
                <button type="submit" class="btn">Отправить</button>
            </form>
        </div>
    </div>
</section>
<?php site_footer(); ?>
