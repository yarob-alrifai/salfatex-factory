<?php
require_once __DIR__ . '/_layout.php';
require_admin();

$messages = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();

admin_header('Messages');
?>
<div class="admin-card">
    <div class="admin-card__header">
        <div>
            <p class="eyebrow">طلبات التعاون</p>
            <h2>الرسائل الواردة من نموذج الموقع</h2>
            <p class="subtitle">يمكنك متابعة رسائل "أرسل لنا رسالة" والتواصل مع العملاء</p>
        </div>
        <div class="stat">
            <span class="stat__label">إجمالي الرسائل</span>
            <strong class="stat__value"><?php echo count($messages); ?></strong>
        </div>
    </div>
    <?php if (!$messages): ?>
        <p class="empty-state">لا توجد رسائل بعد. جرّب إرسال رسالة من نموذج الموقع.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الاتصال</th>
                    <th>الرسالة</th>
                    <th>تاريخ الإرسال</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $message):
                    $isUnread = empty($message['is_read']);
                    $rowClasses = 'message-row';
                    if ($isUnread) {
                        $rowClasses .= ' is-unread';
                    }
                    ?>
                    <tr class="<?php echo h($rowClasses); ?>" data-message-row="messages_view.php?id=<?php echo (int)$message['id']; ?>" tabindex="0" role="button">
                        <td><?php echo (int)$message['id']; ?></td>
                        <td>
                            <strong><?php echo h($message['name']); ?></strong>
                        </td>
                        <td>
                            <div class="stacked">
                                <span>📞 <?php echo h($message['phone']); ?></span>
                                <span>✉️ <a data-row-ignore href="mailto:<?php echo h($message['email']); ?>"><?php echo h($message['email']); ?></a></span>
                            </div>
                        </td>
                        <td>
                            <div class="message-preview"><?php echo nl2br(h($message['message'])); ?></div>
                        </td>
                        <td><?php echo h(date('Y-m-d H:i', strtotime($message['created_at']))); ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn-secondary btn-small" data-row-ignore href="messages_view.php?id=<?php echo (int)$message['id']; ?>">قراءة</a>
                                <a class="btn-danger btn-small" data-row-ignore href="messages_delete.php?id=<?php echo (int)$message['id']; ?>" onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');">حذف</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-message-row]').forEach((row) => {
            const href = row.dataset.messageRow;
            if (!href) {
                return;
            }
            row.addEventListener('click', () => {
                window.location.href = href;
            });
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    window.location.href = href;
                }
            });
        });

        document.querySelectorAll('[data-row-ignore]').forEach((el) => {
            el.addEventListener('click', (event) => event.stopPropagation());
        });
    });
</script>
<?php admin_footer(); ?>
