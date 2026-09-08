<script setup lang="ts">
/**
 * Public template card — preview image + hover overlay with
 * "Copy prompt" (anonymous AI flow) and "Customize it" (portal).
 */
import { resolvePublicFileUrl } from '~/composables/usePortalApi';
import {
    buildTemplatePrompt,
    prefetchCvSkill,
    type TemplateKind,
} from '~/composables/useTemplatePrompt';
import { useLocalizedTemplate } from '~/composables/useLocalizedTemplate';
import { copyToClipboard } from '~/utils/clipboard';

export type PublicTemplate = {
    id: number;
    name: string;
    preview?: string | null;
    description?: string | null;
    supports_image?: boolean;
    is_default?: boolean;
};

const props = withDefaults(defineProps<{
    template: PublicTemplate;
    kind?: TemplateKind;
}>(), {
    kind: 'cv',
});

const emit = defineEmits<{
    customize: [template: PublicTemplate];
}>();

const { t } = useI18n();
const toast = useToast();
const { label, description } = useLocalizedTemplate();
const config = useRuntimeConfig();
const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');

const copying = ref(false);
const copied = ref(false);
const imgFailed = ref(false);

watch(() => props.template.id, () => { imgFailed.value = false; });

onMounted(() => {
    prefetchCvSkill();
});

const title = computed(() => label(props.kind, props.template.name));
const blurb = computed(() => description(props.kind, props.template.name, props.template.description));

const previewSrc = computed(() => {
    if (imgFailed.value) return '';
    const fromApi = props.template.preview ? resolvePublicFileUrl(props.template.preview) : '';
    if (fromApi) return fromApi;
    if (props.kind === 'cover-letter') return '';
    return `${laravel}/images/templates/${props.template.name}.png`;
});

async function onCopyPrompt() {
    if (copying.value) return;
    copying.value = true;
    try {
        const text = await buildTemplatePrompt({
            ...props.template,
            kind: props.kind,
            displayName: title.value,
            blurb: blurb.value,
        });
        await copyToClipboard(text);
        copied.value = true;
        toast.success(t('landing.templates_page.prompt_copied'));
        window.setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        toast.error(t('landing.templates_page.prompt_copy_failed'));
    } finally {
        copying.value = false;
    }
}

function onCustomize() {
    emit('customize', props.template);
}
</script>

<template>
    <article class="tpl-tile tpl-tile--interactive">
        <div class="tpl-preview tpl-preview--media">
            <img
                v-if="previewSrc && !imgFailed"
                :src="previewSrc"
                :alt="title"
                class="tpl-preview__img"
                loading="lazy"
                @error="imgFailed = true"
            >
            <div v-else class="tpl-preview__fallback" aria-hidden="true">
                <div class="tpl-mock">
                    <div class="tpl-mock__header">
                        <div class="tpl-mock__avatar" />
                        <div class="tpl-mock__title">
                            <span /><span class="short" />
                        </div>
                    </div>
                    <div class="tpl-mock__line" v-for="i in 5" :key="i" :style="{ width: [95, 80, 90, 70, 60][i - 1] + '%' }" />
                    <div class="tpl-mock__section" />
                    <div class="tpl-mock__line" v-for="i in 4" :key="'b' + i" :style="{ width: [90, 85, 92, 75][i - 1] + '%' }" />
                </div>
            </div>

            <div class="tpl-tile__overlay">
                <Button
                    size="sm"
                    variant="secondary"
                    :loading="copying"
                    @click="onCopyPrompt"
                >
                    <Icon name="copy" :size="15" />
                    {{ copied ? t('landing.templates_page.prompt_copied') : t('landing.templates_page.copy_prompt') }}
                </Button>
                <Button size="sm" variant="primary" @click="onCustomize">
                    <Icon name="edit" :size="15" />
                    {{ t('landing.templates_page.customize') }}
                </Button>
            </div>
        </div>

        <div class="tpl-meta">
            <h3>{{ title }}</h3>
            <p v-if="blurb">{{ blurb }}</p>
            <p v-else>{{ t('landing.templates_page.card_fallback') }}</p>
        </div>
    </article>
</template>
