<script setup lang="ts">
import type { HelpArticleSummary } from '~/types/support-api';

const props = defineProps<{
    items: HelpArticleSummary[];
    loading?: boolean;
    loadBody: (slug: string) => Promise<string | null>;
}>();

const { t } = useI18n();
const openSlug = ref<string | null>(null);
const bodyBySlug = ref<Record<string, string>>({});
const loadingSlug = ref<string | null>(null);

async function toggle(slug: string) {
    if (openSlug.value === slug) {
        openSlug.value = null;
        return;
    }
    openSlug.value = slug;
    if (!bodyBySlug.value[slug]) {
        loadingSlug.value = slug;
        const body = await props.loadBody(slug);
        if (body) bodyBySlug.value[slug] = body;
        loadingSlug.value = null;
    }
}
</script>

<template>
    <section id="support-faq" class="support-section" aria-labelledby="support-faq-title">
        <header class="support-section__head">
            <h2 id="support-faq-title">{{ t('support.faq.title') }}</h2>
        </header>

        <p v-if="loading" class="support-muted">{{ t('support.loading') }}</p>
        <p v-else-if="!items.length" class="support-muted">{{ t('support.faq.empty') }}</p>

        <ul v-else class="support-faq">
            <li v-for="item in items" :key="item.id" class="support-faq__item">
                <button
                    type="button"
                    class="support-faq__trigger"
                    :aria-expanded="openSlug === item.slug"
                    @click="toggle(item.slug)"
                >
                    <span>{{ item.title }}</span>
                    <Icon :name="openSlug === item.slug ? 'chevron-up' : 'chevron-down'" :size="18" aria-hidden="true" />
                </button>
                <Collapse :open="openSlug === item.slug">
                    <div class="support-faq__panel">
                        <p v-if="loadingSlug === item.slug" class="support-muted">{{ t('support.loading') }}</p>
                        <p v-else>{{ bodyBySlug[item.slug] || item.excerpt }}</p>
                    </div>
                </Collapse>
            </li>
        </ul>
    </section>
</template>
