<?php
require_once __DIR__ . '/../inc/helpers.php';
$contact = get_contact_info($pdo);
site_header('Контакты фабрики');
?>
<section class="contact bg-white py-16">
    <div class="mx-auto max-w-5xl space-y-10 px-6">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">Контакты</p>
            <h1 class="text-3xl font-semibold text-slate-900">Свяжитесь с командой фабрики</h1>
            <p class="text-slate-600">Ответим на вопросы по ассортименту, частной марке и логистике.</p>
        </div>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                <?php echo h($_SESSION['flash']); unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        <?php if ($contact): ?>
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                        <h2 class="text-2xl font-semibold text-slate-900">Контактная информация</h2>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a class="btn inline-flex items-center justify-center rounded-2xl bg-brand px-4 py-2 text-sm font-semibold text-white shadow-glow" href="tel:<?php echo h($contact['phone_main']); ?>">Позвонить: <?php echo h($contact['phone_main']); ?></a>
                            <?php if (!empty($contact['phone_secondary'])): ?>
                                <a class="btn-secondary inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" href="tel:<?php echo h($contact['phone_secondary']); ?>">Доп. телефон</a>
                            <?php endif; ?>
                            <a class="btn-secondary inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" href="mailto:<?php echo h($contact['email']); ?>">Email</a>
                            <?php if (!empty($contact['whatsapp_link'])): ?>
                                <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" target="_blank" rel="noopener" href="<?php echo h($contact['whatsapp_link']); ?>">WhatsApp</a>
                            <?php endif; ?>
                            <?php if (!empty($contact['telegram_link'])): ?>
                                <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" target="_blank" rel="noopener" href="<?php echo h($contact['telegram_link']); ?>">Telegram</a>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($contact['address'])): ?>
                            <p class="mt-6 text-slate-600"><?php echo nl2br(h($contact['address'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-2xl font-semibold text-slate-900">Связаться с нами</h2>
                        <form id="contactForm" class="mt-6 grid gap-4" method="post" action="send_message.php">
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" name="name" placeholder="Ваше имя" required>
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" name="phone" placeholder="Телефон" required>
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="email" name="email" placeholder="Email" required>
                            <textarea class="min-h-[140px] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" name="message" placeholder="Сообщение" required></textarea>
                            <button type="submit" class="btn inline-flex items-center justify-center rounded-2xl bg-brand px-6 py-3 text-base font-semibold text-white shadow-glow transition hover:bg-brand-dark">Отправить</button>
                        </form>
                    </div>
                </div>
                <div class="map overflow-hidden rounded-3xl border border-slate-200 shadow-lg">
                    <?php echo $contact['map_embed']; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600">
                <p>Контактная информация появится в ближайшее время.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
