<script setup lang="ts">
import type { FeatureRequestItem, HelpArticleSummary } from '~/types/support-api';

const props = withDefaults(defineProps<{
    scope?: 'public' | 'portal';
    showContact?: boolean;
    guideReplaySlug?: string | null;
}>(), {
    scope: 'public',
    showContact: true,
    guideReplaySlug: null,
});

const { t } = useI18n();
const support = useSupportApi(props.scope);
const { user } = useAuthSession();

const faq = ref<HelpArticleSummary[]>([]);
const guides = ref<HelpArticleSummary[]>([]);
const posts = ref<FeatureRequestItem[]>([]);
const loading = ref(true);

async function loadBody(slug: string) {
    const article = await support.showHelpArticle(slug);
    return article?.body ?? null;
}

async function loadAll() {
    loading.value = true;
    const [faqRes, guidesRes, postsRes] = await Promise.all([
        support.listHelpArticles('faq'),
        support.listHelpArticles('guide'),
        support.listFeatureRequests(),
    ]);
    faq.value = faqRes;
    guides.value = guidesRes;
    posts.value = postsRes;
    loading.value = false;
}

async function onVote(id: number) {
    const updated = await support.voteFeatureRequest(id);
    if (updated) {
        posts.value = posts.value.map((p) => (p.id === id ? { ...p, ...updated } : p));
    }
}

async function onSubmitFeature(payload: { title: string; body: string }) {
    await support.createFeatureRequest(payload);
}

async function onSubmitTicket(body: {
    name: string;
    email: string;
    subject: string;
    body: string;
}) {
    const result = await support.submitSupportTicket(body);
    return !!result;
}

onMounted(() => {
    loadAll();
});

watch(() => user.value?.id, () => {
    loadAll();
});
</script>

<template>
    <div class="support-hub">
        <SupportFaqSection :items="faq" :loading="loading" :load-body="loadBody" />
        <SupportGuidesSection
            :items="guides"
            :loading="loading"
            :load-body="loadBody"
            :initial-open-slug="guideReplaySlug"
        />
        <SupportFeatureBoard
            :posts="posts"
            :loading="loading"
            @vote="onVote"
            @submit="onSubmitFeature"
        />
        <SupportMessageForm v-if="showContact" :submit-ticket="onSubmitTicket" />
        <footer class="support-legal">
            <NuxtLink to="/privacy">{{ t('landing.footer_privacy') }}</NuxtLink>
            <span aria-hidden="true">·</span>
            <NuxtLink to="/terms">{{ t('landing.footer_terms') }}</NuxtLink>
        </footer>
    </div>
</template>
