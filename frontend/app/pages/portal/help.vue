<script setup lang="ts">
/**
 * /portal/help — in-app FAQ & guides (portal help-article routes).
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const tour = usePortalTour();
const replaying = ref(false);

async function onReplayTour() {
    replaying.value = true;
    try {
        await tour.replayTourFromHelp();
    } finally {
        replaying.value = false;
    }
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
                <Button variant="secondary" :loading="replaying" @click="onReplayTour">
                    {{ t('portal.help.replay_tour') }}
                </Button>
                <Button to="/support" variant="ghost">
                    {{ t('portal.help.open_public') }}
                </Button>
            </div>
        </header>

        <SupportHub scope="portal" :show-contact="false" />
    </div>
</template>
