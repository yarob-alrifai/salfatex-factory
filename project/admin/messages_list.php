<?php
require_once __DIR__ . '/_i18n.php';
require_once __DIR__ . '/_layout.php';
require_admin();

$perPage = 20;
$messageId = is_string($_GET['id'] ?? null) ? trim($_GET['id']) : '';
$search = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$conditions = [];
$parameters = [];
if ($messageId !== '') {
    // Compare as a string to avoid integer overflow or coercion of invalid IDs.
    $conditions[] = 'CAST(id AS CHAR) = :message_id';
    $parameters[':message_id'] = $messageId;
}
if ($search !== '') {
    $conditions[] = "message LIKE :content ESCAPE '!'";
    $parameters[':content'] = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
}
$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM messages' . $where);
$countStmt->execute($parameters);
$totalMessages = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalMessages / $perPage));
$requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = min($totalPages, max(1, $requestedPage === false ? 1 : $requestedPage));
$offset = ($page - 1) * $perPage;
$stmt = $pdo->prepare('SELECT id, name, phone, email, LEFT(message, 200) AS message, created_at, is_read FROM messages' . $where . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset');
foreach ($parameters as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();
$pageUrl = static function (int $target) use ($messageId, $search): string {
    return '?' . http_build_query(['page' => $target, 'id' => $messageId, 'q' => $search]);
};

admin_header(admin_t('Messages'));
?>
<div class="admin-card">
    <div class="admin-card__header">
        <div>
            <p class="eyebrow"><?php echo h(admin_t('طلبات التعاون')); ?></p>
            <h2><?php echo h(admin_t('الرسائل الواردة من نموذج الموقع')); ?></h2>
            <p class="subtitle"><?php echo h(admin_t('يمكنك متابعة رسائل "أرسل لنا رسالة" والتواصل مع العملاء')); ?></p>
        </div>
        <div class="stat">
            <span class="stat__label"><?php echo h(admin_t('عدد النتائج')); ?></span>
            <strong class="stat__value"><?php echo $totalMessages; ?></strong>
        </div>
    </div>
    <form method="get" action="messages_list.php" class="admin-form">
        <label for="message-search-id"><?php echo h(admin_t('رقم الرسالة')); ?>
            <input type="text" inputmode="numeric" pattern="[0-9]+" id="message-search-id" name="id" value="<?php echo h($messageId); ?>" placeholder="<?php echo h(admin_t('مثال: 25')); ?>">
        </label>
        <label for="message-search-content"><?php echo h(admin_t('البحث في محتوى الرسالة')); ?>
            <input type="search" id="message-search-content" name="q" value="<?php echo h($search); ?>" placeholder="<?php echo h(admin_t('اكتب كلمة أو جزءًا من الرسالة')); ?>">
        </label>
        <div class="table-actions">
            <button type="submit" class="btn btn--primary"><?php echo h(admin_t('بحث')); ?></button>
            <a href="messages_list.php" class="btn-secondary btn-small"><?php echo h(admin_t('عرض جميع الرسائل')); ?></a>
        </div>
        <p class="subtitle"><?php echo h(admin_t('يمكن البحث بأي حقل منفردًا، أو استخدام الحقلين معًا لتضييق النتائج.')); ?></p>
    </form>
    <?php if (!$messages): ?>
        <p class="empty-state"><?php echo h(admin_t('لا توجد رسائل مطابقة. امسح البحث لعرض جميع الرسائل.')); ?></p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo h(admin_t('العميل')); ?></th>
                    <th><?php echo h(admin_t('الاتصال')); ?></th>
                    <th><?php echo h(admin_t('الرسالة')); ?></th>
                    <th><?php echo h(admin_t('تاريخ الإرسال')); ?></th>
                    <th><?php echo h(admin_t('الإجراءات')); ?></th>
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
                                <a class="btn-secondary btn-small" data-row-ignore href="messages_view.php?id=<?php echo (int)$message['id']; ?>"><?php echo h(admin_t('قراءة')); ?></a>
                                <a class="btn-danger btn-small" data-row-ignore href="messages_delete.php?id=<?php echo (int)$message['id']; ?>" onclick="return confirm(<?php echo h(json_encode(admin_t('هل أنت متأكد من حذف هذه الرسالة؟'), JSON_UNESCAPED_UNICODE)); ?>);"><?php echo h(admin_t('حذف')); ?></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <nav class="table-actions" aria-label="<?php echo h(admin_t('صفحات الرسائل')); ?>">
            <span><?php echo h(admin_t('الصفحة')); ?> <?php echo $page; ?> <?php echo h(admin_t('من')); ?> <?php echo $totalPages; ?> <?php echo h(admin_t('— عدد النتائج:')); ?> <?php echo $totalMessages; ?></span>
            <?php if ($page > 1): ?>
                <a class="btn-secondary btn-small" href="<?php echo h($pageUrl(1)); ?>"><?php echo h(admin_t('الأولى')); ?></a>
                <a class="btn-secondary btn-small" href="<?php echo h($pageUrl($page - 1)); ?>" rel="prev"><?php echo h(admin_t('السابقة')); ?></a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a class="btn-secondary btn-small" href="<?php echo h($pageUrl($page + 1)); ?>" rel="next"><?php echo h(admin_t('التالية')); ?></a>
                <a class="btn-secondary btn-small" href="<?php echo h($pageUrl($totalPages)); ?>"><?php echo h(admin_t('الأخيرة')); ?></a>
            <?php endif; ?>
        </nav>
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
