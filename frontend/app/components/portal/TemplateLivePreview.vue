<script setup lang="ts">
/**
 * Scaled A4 HTML preview (Laravel /profile/{id} or /cover-letter/{id}).
 * Updates when the chosen template or revision (saved content) changes.
 */
import { useElementSize } from '@vueuse/core';

export type LivePreviewKind = 'cv' | 'cover-letter';

const props = withDefaults(defineProps<{
    kind: LivePreviewKind;
    documentId: number | string | null | undefined;
    templateId: number | string | null | undefined;
    /** Bump after content saves so the iframe reloads. */
    revision?: number | string;
    loading?: boolean;
}>(), {
    revision: 0,
    loading: false,
});

const { t } = useI18n();
const config = useRuntimeConfig();

/** A4 at 96dpi — matches template `.page { width: 210mm }` */
const PAGE_WIDTH = 794;
const PAGE_HEIGHT = 1123;

const shellRef = ref<HTMLElement | null>(null);
const { width: shellWidth } = useElementSize(shellRef);

const scale = computed(() => {
    const available = shellWidth.value || 0;
    if (available <= 0) return 0.35;
    return Math.min(1, available / PAGE_WIDTH);
});

const scaledHeight = computed(() => Math.ceil(PAGE_HEIGHT * scale.value));

const previewUrl = computed(() => {
    if (!props.documentId || !props.templateId) return '';
    const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');
    const path = props.kind === 'cover-letter'
        ? `/cover-letter/${props.documentId}`
        : `/profile/${props.documentId}`;
    const base = laravel || (import.meta.client ? window.location.origin : '');
    if (!base) return '';
    try {
        const url = new URL(path, base.endsWith('/') ? base : `${base}/`);
        url.searchParams.set('template_id', String(props.templateId));
        url.searchParams.set('embed', '1');
        if (props.revision != null && props.revision !== '') {
            url.searchParams.set('v', String(props.revision));
        }
        return url.toString();
    } catch {
        return '';
    }
});

const iframeSrc = ref('');
const iframeLoading = ref(false);
const iframeFailed = ref(false);

watch(previewUrl, (url) => {
    if (!url) {
        iframeSrc.value = '';
        iframeLoading.value = false;
        iframeFailed.value = false;
        return;
    }
    iframeLoading.value = true;
    iframeFailed.value = false;
    iframeSrc.value = url;
}, { immediate: true });

function onIframeLoad() {
    iframeLoading.value = false;
    iframeFailed.value = false;
}

function onIframeError() {
    iframeLoading.value = false;
    iframeFailed.value = true;
}

function openFullPreview() {
    if (!previewUrl.value) return;
    window.open(previewUrl.value, '_blank', 'noopener');
}

const canPreview = computed(() => Boolean(previewUrl.value));
const showSpinner = computed(() => props.loading || (canPreview.value && iframeLoading.value));
</script>

<template>
    <section class="live-preview" :aria-label="t('portal.common.live_preview')">
        <div class="live-preview__header">
            <div class="live-preview__title-row">
                <span class="live-preview__title">{{ t('portal.common.live_preview') }}</span>
                <span v-if="canPreview" class="live-preview__hint">{{ t('portal.common.live_preview_hint') }}</span>
            </div>
            <button
                v-if="canPreview"
                type="button"
                class="live-preview__open"
                :aria-label="t('portal.common.live_preview_open')"
                @click="openFullPreview"
            >
                <Icon name="external" :size="14" />
            </button>
        </div>

        <div v-if="!templateId" class="live-preview__empty">
            <Icon name="file" :size="22" />
            <p>{{ t('portal.common.live_preview_pick_template') }}</p>
        </div>

        <div v-else-if="!documentId" class="live-preview__empty">
            <Icon name="file" :size="22" />
            <p>{{ t('portal.common.live_preview_unavailable') }}</p>
        </div>

        <div v-else class="live-preview__body">
            <div
                ref="shellRef"
                class="live-preview__shell"
                :style="{ height: `${scaledHeight}px` }"
            >
                <div
                    v-if="showSpinner"
                    class="live-preview__status"
                    aria-live="polite"
                >
                    <span class="live-preview__spinner" aria-hidden="true" />
                    <span>{{ t('portal.common.live_preview_loading') }}</span>
                </div>
                <div
                    v-else-if="iframeFailed"
                    class="live-preview__status live-preview__status--error"
                >
                    <p>{{ t('portal.common.live_preview_failed') }}</p>
                    <button type="button" class="btn btn--secondary btn--sm" @click="iframeSrc = previewUrl">
                        {{ t('portal.common.live_preview_retry') }}
                    </button>
                </div>
                <iframe
                    v-show="iframeSrc && !iframeFailed"
                    class="live-preview__iframe"
                    :src="iframeSrc"
                    :title="t('portal.common.live_preview')"
                    :style="{
                        width: `${PAGE_WIDTH}px`,
                        height: `${PAGE_HEIGHT}px`,
                        transform: `scale(${scale})`,
                    }"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    @load="onIframeLoad"
                    @error="onIframeError"
                />
            </div>
        </div>
    </section>
</template>
