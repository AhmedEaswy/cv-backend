<script setup lang="ts">
/**
 * /portal/help — in-app FAQ & guides (portal help-article routes).
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const support = useSupportApi('portal');

const replaySlug = ref<string | null>(null);

async function replayFirstGuide() {
    const guides = await support.listHelpArticles('guide');
    const first = guides[0];
    if (!first) return;
    replaySlug.value = first.slug;
    await nextTick();
    const el = document.getElementById('support-guides');
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

useHead({
    title: () => `${t('portal.help.title')} — ${appName}`,
});
</script>

<template>
    <div class="portal-page portal-help">
        <header class="portal-page__head">
            <div>
                <p class="portal-page__eyebrow">{{ t('portal.help.eyebrow') }}</p>
                <h1>{{ t('portal.help.title') }}</h1>
                <p class="portal-page__lede">{{ t('portal.help.lede') }}</p>
            </div>
            <div class="portal-help__actions">
                <Button variant="secondary" @click="replayFirstGuide">
                    {{ t('portal.help.replay_guide') }}
                </Button>
                <Button to="/support" variant="ghost">
                    {{ t('portal.help.open_public') }}
                </Button>
            </div>
        </header>

        <SupportHub scope="portal" :show-contact="false" :guide-replay-slug="replaySlug" />
    </div>
</template>
