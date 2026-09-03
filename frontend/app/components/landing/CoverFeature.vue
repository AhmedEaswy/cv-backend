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
    <section id="cover" class="feature-section">
        <div class="feature-section__container">
            <div class="feature-section__media">
                <div class="feature-section__mock-wrap">
                    <div class="sheet sheet--cover">
                        <div class="sheet__avatars">
                            <div class="sheet__avatar sheet__avatar--primary" />
                            <div class="sheet__avatar sheet__avatar--midnight" />
                        </div>

                        <div class="sheet__block">
                            <div class="sheet__label">
                                {{ t('landing.cover_label_to') }}
                            </div>
                            <div class="sheet-line sheet-line--55" />
                        </div>

                        <div class="sheet__block">
                            <div class="sheet__label">
                                {{ t('landing.cover_label_subject') }}
                            </div>
                            <div class="sheet-line sheet-line--75" />
                        </div>

                        <div class="sheet__lines">
                            <div class="sheet-line thin sheet-line--98" />
                            <div class="sheet-line thin sheet-line--95" />
                            <div class="sheet-line thin sheet-line--90" />
                            <div class="sheet-line thin sheet-line--92" />
                            <div class="sheet-line thin sheet-line--85" />
                        </div>

                        <div class="sheet__lines">
                            <div class="sheet-line thin sheet-line--96" />
                            <div class="sheet-line thin sheet-line--88" />
                            <div class="sheet-line thin sheet-line--92" />
                            <div class="sheet-line thin sheet-line--70" />
                        </div>

                        <div class="sheet__footer">
                            <div>
                                <div class="sheet-line thin sheet-line--70px mb-1" />
                                <div class="sheet__signoff">
                                    {{ t('landing.cover_label_sincerely') }}
                                </div>
                            </div>
                            <div class="chip chip-violet">{{ t('landing.cover_label_matching') }}</div>
                        </div>
                    </div>

                    <div class="feature-badge feature-badge--round feature-badge--brand" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="feature-section__copy">
                <div class="feature-section__eyebrow">
                    {{ t('landing.cover_eyebrow') }}
                </div>
                <h2 class="feature-section__title">
                    {{ t('landing.cover_title') }}
                </h2>
                <p class="feature-section__subtitle">
                    {{ t('landing.cover_subtitle') }}
                </p>

                <ul class="feature-section__list">
                    <li v-for="f in feats" :key="f" class="feature-section__list-item">
                        <span class="feature-section__check">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="feature-section__check-text">{{ t(`landing.cover_feat_${f}`) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
