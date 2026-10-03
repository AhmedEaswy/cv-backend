<script setup lang="ts">
import type { HelpArticleSummary } from '~/types/support-api';

const props = defineProps<{
    items: HelpArticleSummary[];
    loading?: boolean;
    loadBody: (slug: string) => Promise<string | null>;
    /** When set, expand this guide slug on mount (replay from Help). */
    initialOpenSlug?: string | null;
}>();

const { t } = useI18n();
const expandedSlug = ref<string | null>(null);
const bodyBySlug = ref<Record<string, string>>({});
const loadingSlug = ref<string | null>(null);

async function openGuide(slug: string) {
    expandedSlug.value = expandedSlug.value === slug ? null : slug;
    if (expandedSlug.value && !bodyBySlug.value[slug]) {
        loadingSlug.value = slug;
        const body = await props.loadBody(slug);
        if (body) bodyBySlug.value[slug] = body;
        loadingSlug.value = null;
    }
}

onMounted(async () => {
    if (props.initialOpenSlug) {
        expandedSlug.value = props.initialOpenSlug;
        const body = await props.loadBody(props.initialOpenSlug);
        if (body) bodyBySlug.value[props.initialOpenSlug] = body;
    }
});

watch(() => props.initialOpenSlug, async (slug) => {
    if (!slug) return;
    expandedSlug.value = slug;
    if (!bodyBySlug.value[slug]) {
        loadingSlug.value = slug;
        const body = await props.loadBody(slug);
        if (body) bodyBySlug.value[slug] = body;
        loadingSlug.value = null;
    }
});
</script>

<template>
    <section id="support-guides" class="support-section" aria-labelledby="support-guides-title">
        <header class="support-section__head">
            <h2 id="support-guides-title">{{ t('support.guides.title') }}</h2>
            <p class="support-section__lede">{{ t('support.guides.lede') }}</p>
        </header>

        <p v-if="loading" class="support-muted">{{ t('support.loading') }}</p>
        <p v-else-if="!items.length" class="support-muted">{{ t('support.guides.empty') }}</p>

        <ul v-else class="support-guides">
            <li v-for="guide in items" :key="guide.id" class="support-guides__item">
                <button type="button" class="support-guides__card" @click="openGuide(guide.slug)">
                    <div>
                        <h3>{{ guide.title }}</h3>
                        <p>{{ guide.excerpt }}</p>
                    </div>
                    <Icon name="chevron-right" :size="18" aria-hidden="true" />
                </button>
                <Collapse :open="expandedSlug === guide.slug">
                    <div class="support-guides__body">
                        <p v-if="loadingSlug === guide.slug" class="support-muted">{{ t('support.loading') }}</p>
                        <p v-else>{{ bodyBySlug[guide.slug] }}</p>
                    </div>
                </Collapse>
            </li>
        </ul>
    </section>
</template>
