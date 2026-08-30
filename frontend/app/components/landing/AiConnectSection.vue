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

<style scoped>
.ai-connect { background: var(--color-paper); }
.ai-connect__grid {
    display: grid;
    gap: 2.5rem;
    align-items: center;
}
@media (min-width: 900px) {
    .ai-connect__grid { grid-template-columns: 1fr 1fr; }
}
.ai-connect h2 { margin: 0.4rem 0 0.85rem; max-width: 18ch; }
.ai-connect .lede { max-width: 36rem; }
.ai-connect__bullets {
    margin: 0 0 1.4rem;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.45rem;
}
.ai-connect__bullets li {
    padding-inline-start: 1.4rem;
    position: relative;
    color: var(--color-ink-soft);
    font-size: 0.95rem;
}
.ai-connect__bullets li::before {
    content: '';
    position: absolute;
    inset-inline-start: 0;
    top: 0.45rem;
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 50%;
    background: var(--color-ink);
}
</style>
