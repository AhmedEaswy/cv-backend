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

const copying = ref(false);

watch(open, (isOpen) => {
    if (isOpen) prefetchCvSkill();
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
        toast.success(t('landing.templates_page.prompt_copied'));
    } catch {
        toast.error(t('landing.templates_page.prompt_copy_failed'));
    } finally {
        copying.value = false;
    }
}
</script>

<template>
    <Modal v-model:open="open" :title="t('landing.templates_page.auth_modal_title')" size="md">
        <p class="tpl-auth-modal__lead">
            {{ t('landing.templates_page.auth_modal_lead') }}
        </p>

        <ul class="tpl-auth-modal__bullets">
            <li>{{ t('landing.templates_page.auth_modal_bullet_account') }}</li>
            <li>{{ t('landing.templates_page.auth_modal_bullet_prompt') }}</li>
        </ul>

        <template #footer>
            <div class="tpl-auth-modal__actions">
                <Button variant="ghost" size="sm" :loading="copying" @click="copyPrompt">
                    <Icon name="copy" :size="15" />
                    {{ t('landing.templates_page.copy_prompt') }}
                </Button>
                <div class="tpl-auth-modal__auth">
                    <Button :to="loginUrl" variant="secondary" size="sm" @click="open = false">
                        {{ t('landing.nav.login') }}
                    </Button>
                    <Button :to="createUrl" variant="primary" size="sm" @click="open = false">
                        {{ t('landing.footer_create_account') }}
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
