<script setup lang="ts">
/**
 * <CoverFeature />
 *
 * Two-column section showcasing the cover letter feature. The left
 * column is a decorative cover letter "sheet" mockup; the right is
 * eyebrow + title + subtitle + bullet list.
 */
const { t } = useI18n();

const feats = ['match', 'tone', 'export', 'rtl'] as const;
</script>

<template>
    <section id="cover" class="py-20 sm:py-28">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 order-2 lg:order-1">
                <div class="relative max-w-md mx-auto">
                    <div class="sheet p-7 rotate-[-2deg]" style="background: #fffefb;">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-7 h-7 rounded-full" style="background: var(--color-brand-primary);"></div>
                            <div class="w-7 h-7 rounded-full -ms-2.5" style="background: var(--color-brand-midnight); border: 2px solid #fffefb;"></div>
                        </div>

                        <div class="mb-4">
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5">
                                {{ t('landing.cover_label_to') }}
                            </div>
                            <div class="sheet-line" style="width: 55%;"></div>
                        </div>

                        <div class="mb-4">
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-[#8a8499] mb-1.5">
                                {{ t('landing.cover_label_subject') }}
                            </div>
                            <div class="sheet-line" style="width: 75%;"></div>
                        </div>

                        <div class="space-y-1.5 mb-5">
                            <div class="sheet-line thin" style="width: 98%;"></div>
                            <div class="sheet-line thin" style="width: 95%;"></div>
                            <div class="sheet-line thin" style="width: 90%;"></div>
                            <div class="sheet-line thin" style="width: 92%;"></div>
                            <div class="sheet-line thin" style="width: 85%;"></div>
                        </div>

                        <div class="space-y-1.5 mb-5">
                            <div class="sheet-line thin" style="width: 96%;"></div>
                            <div class="sheet-line thin" style="width: 88%;"></div>
                            <div class="sheet-line thin" style="width: 92%;"></div>
                            <div class="sheet-line thin" style="width: 70%;"></div>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <div>
                                <div class="sheet-line thin mb-1" style="width: 70px;"></div>
                                <div class="text-[10px] font-semibold" style="color: var(--color-brand-primary);">
                                    {{ t('landing.cover_label_sincerely') }}
                                </div>
                            </div>
                            <div class="chip chip-violet">{{ t('landing.cover_label_matching') }}</div>
                        </div>
                    </div>

                    <div
                        class="absolute -top-4 -end-4 w-16 h-16 rounded-full flex items-center justify-center shadow-lg"
                        style="background: var(--color-brand-primary); color: #fff;"
                        aria-hidden="true"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 order-1 lg:order-2">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--color-brand-primary);">
                    {{ t('landing.cover_eyebrow') }}
                </div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-4 leading-tight">
                    {{ t('landing.cover_title') }}
                </h2>
                <p class="text-[#4a4458] text-lg leading-relaxed mb-6">
                    {{ t('landing.cover_subtitle') }}
                </p>

                <ul class="space-y-3">
                    <li v-for="f in feats" :key="f" class="flex items-start gap-3">
                        <span
                            class="inline-flex items-center justify-center w-5 h-5 rounded-full mt-0.5 flex-shrink-0"
                            style="background: var(--color-brand-primary-soft); color: var(--color-brand-primary);"
                        >
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="text-[#4a4458] leading-relaxed">{{ t(`landing.cover_feat_${f}`) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
