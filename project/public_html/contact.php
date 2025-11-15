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
<section class="contact bg-gradient-to-b from-slate-50 via-white to-slate-100 py-20">
    <div class="mx-auto max-w-6xl space-y-12 px-6">
        <div class="mx-auto max-w-3xl text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-4 py-1 text-xs font-semibold tracking-[0.4em] text-sky-600">
                <span class="inline-block h-2 w-2 rounded-full bg-sky-500"></span>
                <span>اتصل بنا</span>
            </div>
            <h1 class="mt-5 text-4xl font-semibold text-slate-900 sm:text-5xl"><?php echo h($h1); ?></h1>
            <p class="mt-4 text-lg leading-8 text-slate-600">نعرف أن نجاح أعمالك يعتمد على شريك موثوق. لهذا السبب وفرنا لك خطوط اتصال مباشرة مع فريق المبيعات والخبراء اللوجستيين لدعمك في كل مرحلة من مراحل الطلب والشحن.</p>
        </div>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert mx-auto max-w-3xl rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-center text-emerald-800 shadow-sm">
                <?php echo h($_SESSION['flash']); unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
        <?php if ($contact): ?>
            <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-8">
                    <div class="rounded-[32px] border border-slate-200 bg-white/80 p-8 shadow-xl shadow-slate-200/60">
                        <div class="space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand">تفاصيل الاتصال</p>
                            <h2 class="text-3xl font-semibold text-slate-900">قنوات سريعة للتواصل المباشر</h2>
                            <p class="text-base text-slate-600">نؤمن فريق دعم متعدد اللغات يعمل طوال أيام الأسبوع لتقديم الاستشارات الفنية والتجارية.</p>
                        </div>
                        <div class="mt-8 grid gap-4 md:grid-cols-2">
                            <div class="info-card rounded-2xl border border-slate-100 bg-slate-50/80 p-5">
                                <p class="info-label text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">الهاتف الرئيسي</p>
                                <a class="info-action mt-2 block text-2xl font-semibold text-slate-900" href="tel:<?php echo h($contact['phone_main']); ?>"><?php echo h($contact['phone_main']); ?></a>
                                <p class="text-sm text-slate-500">دعم المبيعات وخدمة العملاء</p>
                            </div>
                            <?php if (!empty($contact['phone_secondary'])): ?>
                                <div class="info-card rounded-2xl border border-slate-100 bg-slate-50/80 p-5">
                                    <p class="info-label text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">هاتف إضافي</p>
                                    <a class="info-action mt-2 block text-2xl font-semibold text-slate-900" href="tel:<?php echo h($contact['phone_secondary']); ?>"><?php echo h($contact['phone_secondary']); ?></a>
                                    <p class="text-sm text-slate-500">مخصص للشحن والمتابعة اللوجستية</p>
                                </div>
                            <?php endif; ?>
                            <div class="info-card rounded-2xl border border-slate-100 bg-slate-50/80 p-5">
                                <p class="info-label text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">البريد الإلكتروني</p>
                                <a class="info-action mt-2 block text-lg font-semibold text-sky-600" href="mailto:<?php echo h($contact['email']); ?>"><?php echo h($contact['email']); ?></a>
                                <p class="text-sm text-slate-500">نرد خلال أقل من 24 ساعة عمل</p>
                            </div>
                            <?php if (!empty($contact['address'])): ?>
                                <div class="info-card rounded-2xl border border-slate-100 bg-slate-50/80 p-5">
                                    <p class="info-label text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">مركز العمليات</p>
                                    <p class="info-text mt-2 text-base text-slate-800"><?php echo nl2br(h($contact['address'])); ?></p>
                                    <p class="text-sm text-slate-500">زيارتك مرحب بها عبر موعد مسبق</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-8 rounded-2xl bg-gradient-to-r from-sky-50 to-slate-50/70 p-5">
                            <p class="text-sm font-semibold text-slate-800">ساعات العمل</p>
                            <p class="mt-1 text-base text-slate-600">الإثنين – الجمعة من 9:00 صباحاً حتى 6:00 مساءً بتوقيت موسكو. فريق الطوارئ متوفر عبر واتساب خارج هذه الأوقات.</p>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <?php if (!empty($contact['whatsapp_link'])): ?>
                                <a class="contact-pill inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-5 py-2 text-sm font-semibold text-emerald-600 transition hover:bg-emerald-500/20" target="_blank" rel="noopener" href="<?php echo h($contact['whatsapp_link']); ?>">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                    واتساب مباشر
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($contact['telegram_link'])): ?>
                                <a class="contact-pill inline-flex items-center gap-2 rounded-full bg-sky-500/10 px-5 py-2 text-sm font-semibold text-sky-600 transition hover:bg-sky-500/20" target="_blank" rel="noopener" href="<?php echo h($contact['telegram_link']); ?>">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                                    تيليجرام فوري
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="rounded-[32px] border border-slate-200 bg-slate-900 px-8 py-10 text-white shadow-2xl shadow-slate-900/20">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-white/60">لماذا تختار سلفاتكس</p>
                        <h2 class="mt-4 text-3xl font-semibold">نهج شخصي يسرّع من تجربة الشراء</h2>
                        <ul class="mt-6 space-y-4 text-base leading-relaxed text-white/80">
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                                متابعة آنية لكل طلبية عبر مدير حساب خاص بك.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-sky-400"></span>
                                تقارير حالة الإنتاج والشحن ترسل تلقائياً عبر البريد أو واتساب.
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-fuchsia-400"></span>
                                دعم فني لتخصيص المنتجات والمواد خلال 48 ساعة.
                            </li>
                        </ul>
                        <div class="mt-8 rounded-2xl bg-white/10 p-5 text-white">
                            <p class="text-sm text-white/70">تحتاج استشارة سريعة؟</p>
                            <p class="text-lg font-semibold">اتصل الآن وسنرتب مكالمة فيديو لتقديم عرض الأسعار مباشرة.</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-slate-200 bg-white/80 p-6 shadow-xl shadow-slate-200/60">
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-brand">مخطط القنوات</p>
                        <h3 class="mt-3 text-2xl font-semibold text-slate-900">اختر المسار الأنسب لاحتياجك</h3>
                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-sm font-semibold text-slate-600">طلبات التوريد الكبيرة</p>
                                <p class="text-sm text-slate-500">الهاتف الرئيسي أو البريد مع ذكر كمية الطلب والوجهة.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-sm font-semibold text-slate-600">التصاميم الخاصة والعينات</p>
                                <p class="text-sm text-slate-500">تواصل مع مدير المنتجات عبر واتساب لمشاركة المواصفات.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                                <p class="text-sm font-semibold text-slate-600">دعم ما بعد البيع</p>
                                <p class="text-sm text-slate-500">نوصي بالتواصل عبر البريد ليتابع الفريق الفني الحالة بالكامل.</p>
                            </div>
                        </div>
                    </div>
                    <div class="map overflow-hidden rounded-[32px] border border-slate-200 shadow-2xl shadow-slate-200/70">
                        <?php if ($mapEmbed): ?>
                            <?php echo $mapEmbed; ?>
                        <?php else: ?>
                            <div class="flex h-full min-h-[320px] w-full items-center justify-center bg-slate-50 text-slate-500">
                                <p>Добавьте карту в панели администратора, чтобы показать адрес на странице контактов.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($contact && !empty($contact['seo_text'])): ?>
            <div class="prose prose-slate mx-auto max-w-4xl rounded-[32px] border border-slate-200 bg-white px-8 py-8 text-slate-700 shadow-sm"><?php echo safe_html($contact['seo_text']); ?></div>
        <?php endif; ?>
        <?php if (!$contact): ?>
            <div class="rounded-[32px] border border-dashed border-slate-300 bg-white/80 px-6 py-12 text-center text-slate-600 shadow-sm">
                <p>Контактная информация появится в ближайшее время.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php site_footer(); ?>
