<script setup lang="ts">
/**
 * /templates — public gallery of CV + cover-letter templates with
 * preview, AI prompt copy, load-more pagination, and auth-gated customize.
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

const { t } = useI18n();
const config = useRuntimeConfig();
const appName = config.public.appName as string;
const { user } = await useAuth();
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

const kind = computed<TemplateKind>(() => (
    route.query.type === 'cover-letter' ? 'cover-letter' : 'cv'
));

const endpoint = computed(() => (
    kind.value === 'cover-letter' ? '/cover-letters/templates' : '/shares/templates'
));

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
            query: { page: pageNum, per_page: PER_PAGE },
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

async function setKind(next: TemplateKind) {
    if (next === kind.value) return;
    await router.replace({
        query: next === 'cover-letter' ? { type: 'cover-letter' } : {},
    });
}

async function loadMore() {
    if (!hasMore.value || loadingMore.value) return;
    await fetchPage(page.value + 1, true);
}

const authOpen = ref(false);
const pendingTemplate = ref<PublicTemplate | null>(null);

function onCustomize(template: PublicTemplate) {
    if (user.value) {
        const path = kind.value === 'cover-letter'
            ? `/portal/cover-letters/create?cover_letter_template_id=${template.id}`
            : `/portal/cvs/create?template_id=${template.id}`;
        navigateTo(path);
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
