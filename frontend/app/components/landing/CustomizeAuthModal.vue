<script setup lang="ts">
/**
 * Shown when a guest clicks "Customize it" on a public template card.
 * Offers register / login, or copy the AI prompt as an alternative.
 */
import type { PublicTemplate } from '~/components/landing/TemplateCard.vue';
import { buildTemplatePrompt, prefetchCvSkill, type TemplateKind } from '~/composables/useTemplatePrompt';
import { copyToClipboard } from '~/utils/clipboard';
import { useLocalizedTemplate } from '~/composables/useLocalizedTemplate';

const open = defineModel<boolean>('open', { default: false });

const props = withDefaults(defineProps<{
    template: PublicTemplate | null;
    kind?: TemplateKind;
}>(), {
    kind: 'cv',
});

const { t } = useI18n();
const toast = useToast();
const { label, description } = useLocalizedTemplate();
const config = useRuntimeConfig();
const laravel = (config.public.laravelUrl as string).replace(/\/+$/, '');
const authIconSrc = `${laravel}/images/auth.svg`;

const copying = ref(false);
const copied = ref(false);

const templateLabel = computed(() => {
    if (!props.template) return '';
    return label(props.kind, props.template.name);
});

watch(open, (isOpen) => {
    if (isOpen) {
        copied.value = false;
        prefetchCvSkill();
    }
});

function customizePath(template: PublicTemplate) {
    if (props.kind === 'cover-letter') {
        return `/portal/cover-letters/create?cover_letter_template_id=${template.id}`;
    }
    return `/portal/cvs/create?template_id=${template.id}`;
}

const createUrl = computed(() => {
    if (!props.template) return '/auth/register';
    const redirect = customizePath(props.template);
    return `/auth/register?redirect=${encodeURIComponent(redirect)}`;
});

const loginUrl = computed(() => {
    if (!props.template) return '/auth/login';
    const redirect = customizePath(props.template);
    return `/auth/login?redirect=${encodeURIComponent(redirect)}`;
});

async function copyPrompt() {
    if (!props.template || copying.value) return;
    copying.value = true;
    try {
        const tpl = props.template;
        const text = await buildTemplatePrompt({
            ...tpl,
            kind: props.kind,
            displayName: label(props.kind, tpl.name),
            blurb: description(props.kind, tpl.name, tpl.description),
        });
        await copyToClipboard(text);
        copied.value = true;
        toast.success(t('landing.templates_page.prompt_copied'));
    } catch {
        toast.error(t('landing.templates_page.prompt_copy_failed'));
    } finally {
        copying.value = false;
    }
}
</script>

<template>
    <Modal v-model:open="open" size="sm">
        <template #header>
            <span class="sr-only">{{ t('landing.templates_page.auth_modal_title') }}</span>
        </template>

        <div class="tpl-auth-modal">
            <div class="tpl-auth-modal__hero">
                <div class="tpl-auth-modal__icon-wrap" aria-hidden="true">
                    <img
                        class="tpl-auth-modal__icon"
                        :src="authIconSrc"
                        alt=""
                        width="72"
                        height="72"
                        decoding="async"
                    >
                </div>
                <h3 class="tpl-auth-modal__title">
                    {{ t('landing.templates_page.auth_modal_title') }}
                </h3>
                <!-- <p v-if="templateLabel" class="tpl-auth-modal__template">
                    {{ templateLabel }}
                </p> -->
                <p class="tpl-auth-modal__lead">
                    {{ t('landing.templates_page.auth_modal_lead') }}
                </p>
            </div>

            <div class="tpl-auth-modal__paths">
                <section class="tpl-auth-modal__path tpl-auth-modal__path--primary">
                    <!-- <p class="tpl-auth-modal__path-text">
                        {{ t('landing.templates_page.auth_modal_bullet_account') }}
                    </p> -->
                    <div class="tpl-auth-modal__cta">
                        <Button :to="createUrl" variant="primary" block @click="open = false">
                            {{ t('landing.footer_create_account') }}
                        </Button>
                        <Button :to="loginUrl" variant="secondary" block @click="open = false">
                            {{ t('landing.nav.login') }}
                        </Button>
                    </div>
                </section>

                <div class="tpl-auth-modal__divider" role="separator">
                    <span>{{ t('auth.or') }}</span>
                </div>

                <section class="tpl-auth-modal__path tpl-auth-modal__path--alt">
                    <p class="tpl-auth-modal__path-text">
                        {{ t('landing.templates_page.auth_modal_bullet_prompt') }}
                    </p>
                    <Button
                        variant="ghost"
                        block
                        :loading="copying"
                        @click="copyPrompt"
                    >
                        <Icon :name="copied ? 'check' : 'copy'" :size="16" />
                        {{
                            copied
                                ? t('landing.templates_page.prompt_copied')
                                : t('landing.templates_page.copy_prompt')
                        }}
                    </Button>
                </section>
            </div>
        </div>
    </Modal>
</template>
