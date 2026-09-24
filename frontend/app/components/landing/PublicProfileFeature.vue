<script setup lang="ts">
/**
 * <LandingPublicProfileFeature />
 *
 * Two-column section: real template preview + copy for the shareable /u/ link.
 */
const { t } = useI18n();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');

const previewSrc = `${laravel}/images/public-profile-templates/minimal-folio.png`;
const feats = ['url', 'contact', 'inbox'] as const;
</script>

<template>
    <section id="public-profile" class="section profile-feature" aria-labelledby="profile-feature-title">
        <div class="container-narrow profile-feature__grid">
            <div class="profile-feature__media">
                <div class="profile-feature__frame">
                    <div class="profile-feature__url" aria-hidden="true">
                        <span class="profile-feature__live" />
                        <span class="profile-feature__url-text">/u/your-name</span>
                    </div>
                    <div class="profile-feature__shot">
                        <img
                            :src="previewSrc"
                            :alt="t('landing.profile_image_alt')"
                            class="profile-feature__img"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                </div>
            </div>

            <div class="profile-feature__copy">
                <span class="eyebrow eyebrow--plain">{{ t('landing.profile_eyebrow') }}</span>
                <h2 id="profile-feature-title" class="display-2 profile-feature__title">
                    {{ t('landing.profile_title') }}
                </h2>
                <p class="lede profile-feature__sub">
                    {{ t('landing.profile_subtitle') }}
                </p>
                <ul class="profile-feature__list">
                    <li v-for="f in feats" :key="f">
                        <span class="profile-feature__check" aria-hidden="true">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                        </span>
                        <span>{{ t(`landing.profile_feat_${f}`) }}</span>
                    </li>
                </ul>
                <div class="profile-feature__actions">
                    <NuxtLink to="/templates?type=public-profile" class="btn btn--primary">
                        {{ t('landing.profile_cta') }}
                    </NuxtLink>
                    <NuxtLink to="/portal/public-profile" class="btn btn--secondary">
                        {{ t('landing.profile_cta_secondary') }}
                    </NuxtLink>
                </div>
            </div>
        </div>
    </section>
</template>
