<?php
require_once __DIR__ . '/../inc/helpers.php';
$contact = get_contact_info($pdo);
$mapEmbed = $contact ? sanitize_iframe_embed($contact['map_embed'] ?? '') : '';
$meta = [
    'title' => ($contact ? ($contact['meta_title'] ?? null) : null) ?: 'تواصل معنا — Salfatex',
    'description' => ($contact ? ($contact['meta_description'] ?? null) : null) ?: 'Свяжитесь с фабрикой Salfatex и получите предложение по поставкам.',
    'keywords' => ($contact ? ($contact['meta_keywords'] ?? null) : null) ?: 'контакты, salfatex',
    'canonical' => ($contact ? ($contact['canonical_url'] ?? null) : null) ?: site_url('contact.php'),
    'schema' => [
        [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => 'Контакты Salfatex',
            'url' => ($contact ? ($contact['canonical_url'] ?? null) : null) ?: site_url('contact.php'),
            'description' => strip_tags($contact ? ($contact['seo_text'] ?? '') : '')
        ]
    ]
];
site_header('تواصل معنا', $meta);
$h1 = ($contact['h1'] ?? null) ?: 'تحتاج إلى مساعدة؟ فريق سلفاتكس بانتظارك';
?>
<section class="contact bg-white py-16">
    <div class="mx-auto max-w-5xl space-y-10 px-6">
        <div class="space-y-3 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.4em] text-sky-600">اتصل بنا</p>
            <h1 class="text-3xl font-semibold text-slate-900"><?php echo h($h1); ?></h1>
            <p class="text-slate-600">نوفر لك كل المعلومات المتعلقة بالمنتجات، التوريد، وخدمات ما بعد البيع عبر قنوات تواصل واضحة ومباشرة.</p>
        </div>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800">
                <?php echo h($_SESSION['flash']); unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        <?php if ($contact): ?>
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-8">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                        <div class="space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand">تفاصيل الاتصال</p>
                            <h2 class="text-2xl font-semibold text-slate-900">معلومات التواصل المباشرة</h2>
                            <p class="text-slate-600">اختر الطريقة الأنسب لك للتواصل معنا وسنرد عليك في أسرع وقت ممكن.</p>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="info-card">
                                <p class="info-label">الهاتف الرئيسي</p>
                                <a class="info-action" href="tel:<?php echo h($contact['phone_main']); ?>"><?php echo h($contact['phone_main']); ?></a>
                            </div>
                            <?php if (!empty($contact['phone_secondary'])): ?>
                                <div class="info-card">
                                    <p class="info-label">هاتف إضافي</p>
                                    <a class="info-action" href="tel:<?php echo h($contact['phone_secondary']); ?>"><?php echo h($contact['phone_secondary']); ?></a>
                                </div>
                            <?php endif; ?>
                            <div class="info-card">
                                <p class="info-label">البريد الإلكتروني</p>
                                <a class="info-action" href="mailto:<?php echo h($contact['email']); ?>"><?php echo h($contact['email']); ?></a>
                            </div>
                            <?php if (!empty($contact['address'])): ?>
                                <div class="info-card">
                                    <p class="info-label">العنوان</p>
                                    <p class="info-text"><?php echo nl2br(h($contact['address'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <?php if (!empty($contact['whatsapp_link'])): ?>
                                <a class="contact-pill" target="_blank" rel="noopener" href="<?php echo h($contact['whatsapp_link']); ?>">اضغط هنا من اجل التواصل معنا على واتساب مباشرة</a>
                            <?php endif; ?>
                            <?php if (!empty($contact['telegram_link'])): ?>
                                <a class="contact-pill" target="_blank" rel="noopener" href="<?php echo h($contact['telegram_link']); ?>">اضغط هنا من اجل التواصل معنا على واتساب مباشرة</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-2xl font-semibold text-slate-900">أرسل لنا رسالة</h2>
                        <p class="mt-1 text-sm text-slate-500">أخبرنا بما تحتاجه وسنقوم بالرد عليك عبر الهاتف أو البريد الإلكتروني.</p>
                        <form id="contactForm" class="mt-6 grid gap-4" method="post" action="send_message.php">
                            <?php echo csrf_field('public_contact'); ?>
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" name="name" placeholder="الاسم الكامل" required>
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="text" name="phone" placeholder="رقم الهاتف" required>
                            <input class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" type="email" name="email" placeholder="البريد الإلكتروني" required>
                            <textarea class="min-h-[140px] rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-slate-900 focus:border-brand focus:ring-2 focus:ring-brand/20" name="message" placeholder="أدخل رسالتك أو طلبك" required></textarea>
                            <button type="submit" class="btn inline-flex items-center justify-center rounded-2xl bg-brand px-6 py-3 text-base font-semibold text-white shadow-glow transition hover:bg-brand-dark">إرسال الطلب</button>
                        </form>
                    </div>
                </div>
                <div class="map overflow-hidden rounded-3xl border border-slate-200 shadow-lg">
                    <?php if ($mapEmbed): ?>
                        <?php echo $mapEmbed; ?>
                    <?php else: ?>
                        <div class="flex h-full min-h-[240px] w-full items-center justify-center bg-slate-50 text-slate-500">
                            <p>Добавьте карту в панели администратора, чтобы показать адрес на странице контактов.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($contact && !empty($contact['seo_text'])): ?>
            <div class="rounded-3xl border border-slate-200 bg-white px-6 py-6 text-slate-700 prose prose-slate max-w-3xl mx-auto"><?php echo safe_html($contact['seo_text']); ?></div>
        <?php endif; ?>
        <?php if (!$contact): ?>
            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-600">
                <p>Контактная информация появится в ближайшее время.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
