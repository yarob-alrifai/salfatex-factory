<?php
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

$sentAt = date('Y-m-d H:i', strtotime($message['created_at'] ?? 'now'));
admin_header('رسالة #' . $message['id']);
?>
<div class="admin-card message-details">
    <div class="admin-card__header">
        <div>
            <p class="eyebrow">مراسلات العملاء</p>
            <h2>رسالة من <?php echo h($message['name']); ?></h2>
            <p class="subtitle">تم الاستلام بتاريخ <?php echo h($sentAt); ?></p>
        </div>
        <div class="message-actions">
            <a class="btn-secondary" href="messages_list.php">← عودة إلى جميع الرسائل</a>
            <a class="btn-danger" href="messages_delete.php?id=<?php echo (int)$message['id']; ?>"
               onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');">حذف الرسالة</a>
        </div>
    </div>
    <div class="message-details__grid">
        <div class="message-details__item">
            <p class="message-details__label">معلومات المرسل</p>
            <ul class="message-details__list">
                <li>
                    <span>الاسم</span>
                    <strong><?php echo h($message['name']); ?></strong>
                </li>
                <li>
                    <span>رقم الهاتف</span>
                    <a href="tel:<?php echo h($message['phone']); ?>"><?php echo h($message['phone']); ?></a>
                </li>
                <li>
                    <span>البريد الإلكتروني</span>
                    <a href="mailto:<?php echo h($message['email']); ?>"><?php echo h($message['email']); ?></a>
                </li>
            </ul>
        </div>
        <div class="message-details__item">
            <p class="message-details__label">بيانات الرسالة</p>
            <ul class="message-details__list">
                <li>
                    <span>رقم الرسالة</span>
                    <strong>#<?php echo (int)$message['id']; ?></strong>
                </li>
                <li>
                    <span>تاريخ الإرسال</span>
                    <strong><?php echo h($sentAt); ?></strong>
                </li>
            </ul>
        </div>
    </div>
    <div class="message-body">
        <p class="message-details__label">نص الرسالة</p>
        <div class="message-body__content"><?php echo nl2br(h($message['message'])); ?></div>
    </div>
</div>
<?php admin_footer(); ?>
