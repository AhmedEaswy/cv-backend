<script setup lang="ts">
/**
 * <PublicProfileFeature />
 *
 * Two-column section showcasing the public profile + contact form.
 */
const { t } = useI18n();

const feats = ['url', 'contact', 'inbox'] as const;
</script>

<template>
    <section id="public-profile" class="feature-section feature-section--muted">
        <div class="feature-section__container">
            <div class="feature-section__media">
                <div class="feature-section__mock-wrap">
                    <div class="surface-card profile-mock">
                        <div class="profile-mock__head">
                            <div class="profile-mock__avatar" />
                            <div class="profile-mock__name">
                                <div class="profile-mock__name-bar" />
                                <div class="profile-mock__name-sub" />
                            </div>
                            <span class="chip chip-violet">{{ t('landing.profile_chip_live') }}</span>
                        </div>
                        <div class="profile-mock__lines">
                            <div class="profile-mock__line profile-mock__line--92" />
                            <div class="profile-mock__line profile-mock__line--80" />
                            <div class="profile-mock__line profile-mock__line--88" />
                        </div>
                        <div class="profile-mock__tags">
                            <span class="chip chip-violet">Figma</span>
                            <span class="chip chip-warm">SQL</span>
                            <span class="chip chip-mute">Python</span>
                            <span class="chip chip-violet">REST</span>
                        </div>
                    </div>
                    <div class="feature-badge feature-badge--square feature-badge--brand" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="feature-section__copy">
                <div class="feature-section__eyebrow">
                    {{ t('landing.profile.eyebrow') }}
                </div>
                <h2 class="feature-section__title">
                    {{ t('landing.profile.title') }}
                </h2>
                <p class="feature-section__subtitle">
                    {{ t('landing.profile.subtitle') }}
                </p>
                <ul class="feature-section__list">
                    <li v-for="f in feats" :key="f" class="feature-section__list-item">
                        <span class="feature-section__check">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span class="feature-section__check-text">{{ t(`landing.profile_feat_${f}`) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
