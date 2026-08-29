<script setup lang="ts">
/**
 * <PublicProfileFeature />
 *
 * Two-column section showcasing the public profile + contact form.
 * The left column shows a "public profile" card mockup; the right is
 * eyebrow + title + subtitle + bullet list.
 */
const { t } = useI18n();

const feats = ['url', 'contact', 'inbox'] as const;
</script>

<template>
    <section id="public-profile" class="py-20 sm:py-28" style="background: var(--color-paper-2);">
        <div class="max-w-6xl mx-auto px-5 sm:px-8 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 order-2 lg:order-1">
                <div class="relative max-w-md mx-auto">
                    <div class="surface-card p-7" style="background: #fff;">
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
                            <div
                                style="width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--color-brand-primary), var(--color-brand-midnight));"
                            ></div>
                            <div style="flex: 1;">
                                <div class="sheet-line" style="width: 70%; height: 14px; background: var(--color-brand-midnight); border-radius: 4px;"></div>
                                <div class="sheet-line thin" style="width: 50%; margin-top: 6px; background: #b8b1a3;"></div>
                            </div>
                            <span class="chip chip-violet">{{ t('landing.profile_chip_live') }}</span>
                        </div>
                        <div style="display: grid; gap: 8px;">
                            <div class="sheet-line" style="width: 92%; height: 8px; background: #d9d4c7; border-radius: 4px;"></div>
                            <div class="sheet-line" style="width: 80%; height: 8px; background: #d9d4c7; border-radius: 4px;"></div>
                            <div class="sheet-line" style="width: 88%; height: 8px; background: #d9d4c7; border-radius: 4px;"></div>
                        </div>
                        <div style="margin-top: 18px; display: flex; flex-wrap: wrap; gap: 6px;">
                            <span class="chip chip-violet">Figma</span>
                            <span class="chip chip-warm">SQL</span>
                            <span class="chip chip-mute">Python</span>
                            <span class="chip chip-violet">REST</span>
                        </div>
                    </div>
                    <div
                        class="absolute -top-4 -end-4 w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg"
                        style="background: var(--color-brand-primary); color: #fff;"
                        aria-hidden="true"
                    >
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-6 order-1 lg:order-2">
                <div class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: var(--color-brand-primary);">
                    {{ t('landing.profile.eyebrow') }}
                </div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-[#130e21] mb-4 leading-tight">
                    {{ t('landing.profile.title') }}
                </h2>
                <p class="text-[#4a4458] text-lg leading-relaxed mb-6">
                    {{ t('landing.profile.subtitle') }}
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
                        <span class="text-[#4a4458] leading-relaxed">{{ t(`landing.profile_feat_${f}`) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
