<script setup lang="ts">
const { t } = useI18n();
const { show } = useAiConnectModal();
const track = useClickTracker();

const openConnect = () => {
    track('ai_connect_cta', 'landing');
    show();
};
</script>

<template>
    <section id="ai-connect" class="section ai-connect" aria-labelledby="ai-connect-title">
        <div class="container-narrow ai-connect__grid">
            <div>
                <span class="eyebrow eyebrow--plain">{{ t('landing.ai_connect_eyebrow') }}</span>
                <h2 id="ai-connect-title" class="display-2">{{ t('landing.ai_connect_title') }}</h2>
                <p class="lede">{{ t('landing.ai_connect_subtitle') }}</p>
                <ul class="ai-connect__bullets">
                    <li>{{ t('landing.ai_connect_bullet_cv') }}</li>
                    <li>{{ t('landing.ai_connect_bullet_ats') }}</li>
                    <li>{{ t('landing.ai_connect_bullet_cover') }}</li>
                    <li>{{ t('landing.ai_connect_bullet_profile') }}</li>
                </ul>
                <Button variant="primary" size="md" @click="openConnect">
                    <Icon name="sparkles" :size="16" />
                    {{ t('landing.ai_connect_cta') }}
                </Button>
            </div>
            <LandingAiChatDemo />
        </div>
    </section>
</template>

