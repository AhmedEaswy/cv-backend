<script setup lang="ts">
/**
 * /templates — public gallery of CV, cover-letter, and public-profile templates
 * with preview, AI prompt copy, load-more pagination, and auth-gated customize.
 */
import type { PublicTemplate } from '~/components/landing/TemplateCard.vue';
import { prefetchCvSkill, type TemplateKind } from '~/composables/useTemplatePrompt';

type PaginatedTemplates = {
    data: PublicTemplate[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        has_more: boolean;
    };
};

const PER_PAGE = 9;

const { t, locale } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const { user } = await useAuthUser();
const api = useApi();
const route = useRoute();
const router = useRouter();

useHead({
    title: () => `${t('landing.templates_page.meta_title')} — ${appName}`,
    meta: [
        { name: 'description', content: () => t('landing.templates_page.meta_description') },
        { property: 'og:title', content: () => `${t('landing.templates_page.meta_title')} — ${appName}` },
        { property: 'og:description', content: () => t('landing.templates_page.meta_description') },
        { property: 'og:type', content: 'website' },
    ],
});

const kind = computed<TemplateKind>(() => {
    const type = String(route.query.type || '');
    if (type === 'cover-letter') return 'cover-letter';
    if (type === 'public-profile') return 'public-profile';
    return 'cv';
});

const endpoint = computed(() => {
    if (kind.value === 'cover-letter') return '/cover-letters/templates';
    if (kind.value === 'public-profile') return '/public-profiles/templates';
    return '/shares/templates';
});

const templates = ref<PublicTemplate[]>([]);
const page = ref(1);
const hasMore = ref(false);
const loading = ref(false);
const loadingMore = ref(false);
const loadError = ref(false);

async function fetchPage(pageNum: number, append: boolean) {
    const isMore = append;
    if (isMore) loadingMore.value = true;
    else loading.value = true;
    loadError.value = false;

    try {
        const res = await api<{ result: PaginatedTemplates }>(endpoint.value, {
            query: { page: pageNum, per_page: PER_PAGE, locale: locale.value },
        });
        const payload = res?.result;
        const rows = Array.isArray(payload?.data) ? payload.data : [];
        templates.value = append ? [...templates.value, ...rows] : rows;
        page.value = payload?.meta?.current_page ?? pageNum;
        hasMore.value = !!payload?.meta?.has_more;
    } catch {
        loadError.value = true;
        if (!append) {
            templates.value = [];
            hasMore.value = false;
        }
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
}

await fetchPage(1, false);

onMounted(() => {
    prefetchCvSkill();
});

watch(kind, async (next, prev) => {
    if (next === prev) return;
    templates.value = [];
    hasMore.value = false;
    await fetchPage(1, false);
});

watch(locale, async () => {
    templates.value = [];
    hasMore.value = false;
    await fetchPage(1, false);
});

async function setKind(next: TemplateKind) {
    if (next === kind.value) return;
    const query = next === 'cv'
        ? {}
        : { type: next };
    await router.replace({ query });
}

async function loadMore() {
    if (!hasMore.value || loadingMore.value) return;
    await fetchPage(page.value + 1, true);
}

const authOpen = ref(false);
const pendingTemplate = ref<PublicTemplate | null>(null);

function customizePath(template: PublicTemplate) {
    if (kind.value === 'cover-letter') {
        return `/portal/cover-letters/create?cover_letter_template_id=${template.id}`;
    }
    if (kind.value === 'public-profile') {
        return `/portal/public-profile?public_profile_template_id=${template.id}`;
    }
    return `/portal/cvs/create?template_id=${template.id}`;
}

function onCustomize(template: PublicTemplate) {
    if (user.value) {
        navigateTo(customizePath(template));
        return;
    }
    pendingTemplate.value = template;
    authOpen.value = true;
}
</script>

<template>
    <div class="templates-page">
        <LandingSiteHeader />
        <main>
            <section class="section templates-page__hero">
                <div class="container-narrow">
                    <div class="tpl-head">
                        <span class="eyebrow">{{ t('landing.templates_page.eyebrow') }}</span>
                        <h1 class="display-2 tpl-head__title">{{ t('landing.templates_page.title') }}</h1>
                        <p class="lede tpl-head__sub">{{ t('landing.templates_page.subtitle') }}</p>
                    </div>

                    <div class="templates-page__tabs" role="tablist" :aria-label="t('landing.templates_page.eyebrow')">
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="kind === 'cv'"
                            :class="{ 'is-active': kind === 'cv' }"
                            @click="setKind('cv')"
                        >
                            {{ t('landing.templates_page.tab_cv') }}
                        </button>
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="kind === 'cover-letter'"
                            :class="{ 'is-active': kind === 'cover-letter' }"
                            @click="setKind('cover-letter')"
                        >
                            {{ t('landing.templates_page.tab_cover_letter') }}
                        </button>
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="kind === 'public-profile'"
                            :class="{ 'is-active': kind === 'public-profile' }"
                            @click="setKind('public-profile')"
                        >
                            {{ t('landing.templates_page.tab_public_profile') }}
                        </button>
                    </div>

                    <p v-if="loadError" class="templates-page__error">
                        {{ t('landing.templates_page.load_error') }}
                    </p>

                    <LandingTemplatesGallerySkeleton v-else-if="loading" :count="PER_PAGE" />

                    <div v-else-if="!templates.length" class="templates-page__empty">
                        {{ t('landing.templates_page.empty') }}
                    </div>

                    <template v-else>
                        <div class="tpl-grid">
                            <LandingTemplateCard
                                v-for="tpl in templates"
                                :key="`${kind}-${tpl.id}`"
                                :template="tpl"
                                :kind="kind"
                                @customize="onCustomize"
                            />
                        </div>

                        <div v-if="hasMore" class="templates-page__more">
                            <Button
                                variant="secondary"
                                :loading="loadingMore"
                                @click="loadMore"
                            >
                                {{ t('landing.templates_page.load_more') }}
                            </Button>
                        </div>
                    </template>
                </div>
            </section>
        </main>
        <LandingSiteFooter />

        <LandingCustomizeAuthModal
            v-model:open="authOpen"
            :template="pendingTemplate"
            :kind="kind"
        />
    </div>
</template>
