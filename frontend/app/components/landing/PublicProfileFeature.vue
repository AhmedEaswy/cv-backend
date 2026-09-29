<script setup lang="ts">
/**
 * <LandingPublicProfileFeature />
 *
 * Two-column section: copy + a switchable feature list on one side, a phone
 * mockup rendering the top of a sample mobile portfolio on the other.
 *
 * The active feature advances when its progress bar finishes (animationend),
 * so there are no JS timers. Hovering the list pauses it, and under
 * prefers-reduced-motion the bar never animates, so it stays put.
 */
const { t } = useI18n();

type ProfileFeatureKey = 'url' | 'contact' | 'inbox';

const features: { key: ProfileFeatureKey; icon: string }[] = [
    { key: 'url', icon: 'globe' },
    { key: 'contact', icon: 'mail' },
    { key: 'inbox', icon: 'inbox' },
];

const activeFeature = ref<ProfileFeatureKey>('url');

function selectFeature(key: ProfileFeatureKey) {
    activeFeature.value = key;
}

function advanceFeature() {
    const currentIndex = features.findIndex((feature) => feature.key === activeFeature.value);
    const nextFeature = features[(currentIndex + 1) % features.length];
    if (nextFeature) activeFeature.value = nextFeature.key;
}
</script>

<template>
    <section id="public-profile" class="section profile-feature" aria-labelledby="profile-feature-title">
        <div class="container-narrow profile-feature__grid">
            <div class="profile-feature__copy">
                <span class="eyebrow eyebrow--plain">{{ t('landing.profile_eyebrow') }}</span>
                <h2 id="profile-feature-title" class="display-2 profile-feature__title">
                    {{ t('landing.profile_title') }}
                </h2>
                <p class="lede profile-feature__sub">
                    {{ t('landing.profile_subtitle') }}
                </p>

                <ul class="profile-feature__features">
                    <li v-for="feature in features" :key="feature.key">
                        <button
                            type="button"
                            class="profile-feature__item"
                            :class="{ 'is-active': activeFeature === feature.key }"
                            :aria-pressed="activeFeature === feature.key"
                            @click="selectFeature(feature.key)"
                        >
                            <span class="profile-feature__item-head">
                                <span class="profile-feature__item-icon" aria-hidden="true">
                                    <Icon :name="feature.icon" :size="16" />
                                </span>
                                <span class="profile-feature__item-title">
                                    {{ t(`landing.profile_feat_${feature.key}_title`) }}
                                </span>
                            </span>
                            <span class="profile-feature__item-text">
                                {{ t(`landing.profile_feat_${feature.key}`) }}
                            </span>
                            <span class="profile-feature__item-track" aria-hidden="true">
                                <span
                                    v-if="activeFeature === feature.key"
                                    class="profile-feature__item-progress"
                                    @animationend="advanceFeature"
                                />
                            </span>
                        </button>
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

            <div class="profile-feature__stage" :data-active="activeFeature">
                <div class="profile-phone" role="img" :aria-label="t('landing.profile_image_alt')">
                    <div class="profile-phone__screen" aria-hidden="true">
                        <div class="profile-phone__status" dir="ltr">
                            <span>9:41</span>
                            <span class="profile-phone__island" />
                            <span class="profile-phone__signal">
                                <i /><i /><i />
                            </span>
                        </div>

                        <div class="profile-phone__address" dir="ltr">
                            <Icon name="lock" :size="9" />
                            <span>/u/elena-voss</span>
                        </div>

                        <div class="folio">
                            <div class="folio__hero">
                                <span class="folio__avatar">
                                    <i class="folio__avatar-head" />
                                    <i class="folio__avatar-body" />
                                </span>
                                <span class="folio__name">Elena Voss</span>
                                <span class="folio__role">{{ t('landing.profile_mock_role') }}</span>
                                <span class="folio__chips">
                                    <span class="folio__chip" dir="ltr">elena@example.com</span>
                                    <span class="folio__chip">{{ t('landing.profile_mock_location') }}</span>
                                </span>
                            </div>

                            <div class="folio__card folio__card--rose">
                                <span class="folio__card-title">{{ t('landing.profile_mock_hello') }}</span>
                                <span class="folio__card-text">{{ t('landing.profile_mock_intro') }}</span>
                                <span class="folio__button">{{ t('landing.profile_mock_cta') }}</span>
                            </div>

                            <div class="folio__card folio__card--mint">
                                <span class="folio__card-title">{{ t('landing.profile_mock_offer') }}</span>
                                <span class="folio__card-label">{{ t('landing.profile_mock_service_design') }}</span>
                                <span class="folio__card-text">{{ t('landing.profile_mock_service_design_text') }}</span>
                                <span class="folio__card-label">{{ t('landing.profile_mock_service_systems') }}</span>
                                <span class="folio__card-text">{{ t('landing.profile_mock_service_systems_text') }}</span>
                            </div>

                            <div class="folio__card folio__card--sky">
                                <span class="folio__card-title">{{ t('landing.profile_mock_journey') }}</span>
                                <span class="folio__card-label">{{ t('landing.profile_mock_job') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <span class="profile-float profile-float--url" :class="{ 'is-active': activeFeature === 'url' }" aria-hidden="true">
                    <i class="profile-float__live" />
                    <span class="profile-float__mono" dir="ltr">/u/elena-voss</span>
                    <span class="profile-float__tag">{{ t('landing.profile_mock_live') }}</span>
                </span>

                <span class="profile-float profile-float--contact" :class="{ 'is-active': activeFeature === 'contact' }" aria-hidden="true">
                    <span class="profile-float__icon">
                        <Icon name="mail" :size="13" />
                    </span>
                    <span>{{ t('landing.profile_mock_contact') }}</span>
                    <span class="profile-float__switch" />
                </span>

                <span class="profile-float profile-float--inbox" :class="{ 'is-active': activeFeature === 'inbox' }" aria-hidden="true">
                    <span class="profile-float__sender">M</span>
                    <span class="profile-float__stack">
                        <b>{{ t('landing.profile_mock_message') }}</b>
                        <span>{{ t('landing.profile_mock_message_text') }}</span>
                    </span>
                </span>
            </div>
        </div>
    </section>
</template>
