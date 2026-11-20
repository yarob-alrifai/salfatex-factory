<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM messages WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$message = $stmt->fetch();

if (!$message) {
    header('Location: messages_list.php');
    exit;
}

if (array_key_exists('is_read', $message) && empty($message['is_read'])) {
    $markRead = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = :id LIMIT 1');
    $markRead->execute(['id' => $message['id']]);
    $message['is_read'] = 1;
}

$sentAt = date('Y-m-d H:i', strtotime($message['created_at'] ?? 'now'));
admin_header(admin_t('رسالة #') . $message['id']);
?>
<div class="admin-card message-details">
    <div class="admin-card__header">
        <div>
            <p class="eyebrow"><?php echo h(admin_t('مراسلات العملاء')); ?></p>
            <h2><?php echo h(admin_t('رسالة من')); ?> <?php echo h($message['name']); ?></h2>
            <p class="subtitle"><?php echo h(admin_t('تم الاستلام بتاريخ')); ?> <?php echo h($sentAt); ?></p>
        </div>
        <div class="message-actions">
            <a class="btn-secondary" href="messages_list.php"><?php echo h(admin_t('← عودة إلى جميع الرسائل')); ?></a>
            <a class="btn-danger" href="messages_delete.php?id=<?php echo (int)$message['id']; ?>"
               onclick="return confirm(<?php echo h(json_encode(admin_t('هل أنت متأكد من حذف هذه الرسالة؟'), JSON_UNESCAPED_UNICODE)); ?>);"><?php echo h(admin_t('حذف الرسالة')); ?></a>
        </div>
    </div>
    <div class="message-details__grid">
        <div class="message-details__item">
            <p class="message-details__label"><?php echo h(admin_t('معلومات المرسل')); ?></p>
            <ul class="message-details__list">
                <li>
                    <span><?php echo h(admin_t('الاسم')); ?></span>
                    <strong><?php echo h($message['name']); ?></strong>
                </li>
                <li>
                    <span><?php echo h(admin_t('رقم الهاتف')); ?></span>
                    <a href="tel:<?php echo h($message['phone']); ?>"><?php echo h($message['phone']); ?></a>
                </li>
                <li>
                    <span><?php echo h(admin_t('البريد الإلكتروني')); ?></span>
                    <a href="mailto:<?php echo h($message['email']); ?>"><?php echo h($message['email']); ?></a>
                </li>
            </ul>
        </div>
        <div class="message-details__item">
            <p class="message-details__label"><?php echo h(admin_t('بيانات الرسالة')); ?></p>
            <ul class="message-details__list">
                <li>
                    <span><?php echo h(admin_t('رقم الرسالة')); ?></span>
                    <strong>#<?php echo (int)$message['id']; ?></strong>
                </li>
                <li>
                    <span><?php echo h(admin_t('تاريخ الإرسال')); ?></span>
                    <strong><?php echo h($sentAt); ?></strong>
                </li>
            </ul>
        </div>
    </div>
    <div class="message-body">
        <p class="message-details__label"><?php echo h(admin_t('نص الرسالة')); ?></p>
        <div class="message-body__content"><?php echo nl2br(h($message['message'])); ?></div>
    </div>
</div>
<?php admin_footer(); ?>
