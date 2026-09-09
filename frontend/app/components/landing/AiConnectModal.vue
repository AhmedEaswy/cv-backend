<script setup lang="ts">
import { loadCvSkillText, prefetchCvSkill } from '~/composables/useTemplatePrompt';
import { copyToClipboard } from '~/utils/clipboard';

const { t } = useI18n();
const { user } = await useAuthUser();
const { platforms, laravel } = useAiPlatforms();
const { open } = useAiConnectModal();
const track = useClickTracker();
const toast = useToast();

const tab = ref<'skill' | 'mcp'>('skill');
const copied = ref(false);
const copiedUrl = ref(false);
const skillText = ref('');

watch(open, (isOpen) => {
    if (isOpen) prefetchCvSkill();
});

const mcpUrl = computed(() => `${laravel}/mcp/cv`);
const mcpConfig = computed(() => JSON.stringify({
    mcpServers: {
        cv: { url: mcpUrl.value },
    },
}, null, 2));

const loadSkill = async () => {
    if (skillText.value) return skillText.value;
    const origin = laravel || (import.meta.client ? window.location.origin : '');
    const body = await loadCvSkillText();
    skillText.value = `Origin: ${origin}\n\n${body}`;
    return skillText.value;
};

const copySkill = async () => {
    const text = await loadSkill();
    await copyToClipboard(text);
    copied.value = true;
    track('ai_connect_copy_skill', 'landing');
    toast.success(t('landing.ai_connect_copied'));
    window.setTimeout(() => { copied.value = false; }, 2000);
};

const copyMcpUrl = async () => {
    await copyToClipboard(mcpUrl.value);
    copiedUrl.value = true;
    window.setTimeout(() => { copiedUrl.value = false; }, 2000);
};

const copyMcpConfig = async () => {
    await copyToClipboard(mcpConfig.value);
    toast.success(t('landing.ai_connect_copied'));
};

const openPlatform = async (platform: (typeof platforms)[number]) => {
    await copySkill();
    track(`ai_connect_${platform.id}`, 'landing');
    window.open(platform.url, '_blank', 'noopener,noreferrer');
};

const openOther = async () => {
    await copySkill();
    track('ai_connect_other', 'landing');
};
</script>

<template>
    <Modal v-model:open="open" :title="t('landing.ai_connect_modal_title')" size="lg">
        <p class="ai-modal__lead">{{ t('landing.ai_connect_modal_lead') }}</p>

        <div class="ai-modal__tabs" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'skill'" @click="tab = 'skill'">
                {{ t('landing.ai_connect_tab_skill') }}
            </button>
            <button type="button" role="tab" :aria-selected="tab === 'mcp'" @click="tab = 'mcp'">
                {{ t('landing.ai_connect_tab_mcp') }}
            </button>
        </div>

        <div v-if="tab === 'skill'">
            <ol class="ai-modal__steps">
                <li>{{ t('landing.ai_connect_step_1') }}</li>
                <li>{{ t('landing.ai_connect_step_2') }}</li>
                <li>{{ t('landing.ai_connect_step_3') }}</li>
            </ol>

            <div class="ai-modal__grid">
                <button
                    v-for="platform in platforms"
                    :key="platform.id"
                    type="button"
                    class="ai-modal__platform"
                    :title="platform.name"
                    @click="openPlatform(platform)"
                >
                    <img :src="platform.logo" :alt="platform.name" width="36" height="36" />
                    <span>{{ platform.name }}</span>
                </button>
            </div>

            <button type="button" class="ai-modal__other" @click="openOther">
                <Icon name="sparkles" :size="16" />
                {{ t('landing.ai_connect_other') }}
            </button>
            <p class="ai-modal__hint">{{ t('landing.ai_connect_other_hint') }}</p>
        </div>

        <div v-else class="ai-modal__mcp">
            <p>{{ t('landing.ai_connect_mcp_lead') }}</p>
            <label class="ai-modal__label">{{ t('landing.ai_connect_mcp_url') }}</label>
            <div class="ai-modal__code-row">
                <code>{{ mcpUrl }}</code>
                <Button size="sm" variant="secondary" @click="copyMcpUrl">
                    {{ copiedUrl ? t('landing.ai_connect_copied') : t('landing.ai_connect_mcp_copy_url') }}
                </Button>
            </div>
            <label class="ai-modal__label">{{ t('landing.ai_connect_mcp_config') }}</label>
            <pre class="ai-modal__pre">{{ mcpConfig }}</pre>
            <Button size="sm" variant="ghost" @click="copyMcpConfig">{{ t('landing.ai_connect_copy') }}</Button>
            <p class="ai-modal__hint">{{ t('landing.ai_connect_mcp_token') }}</p>
            <Button
                v-if="user"
                to="/portal/settings/ai-access"
                size="sm"
                variant="secondary"
                @click="open = false"
            >
                {{ t('landing.ai_connect_mcp_token_cta') }}
            </Button>
            <Button v-else to="/auth/register" size="sm" variant="secondary" @click="open = false">
                {{ t('landing.hero_cta_register') }}
            </Button>
        </div>

        <template #footer>
            <Button variant="primary" @click="copySkill">
                <Icon name="copy" :size="15" />
                {{ copied ? t('landing.ai_connect_copied') : t('landing.ai_connect_copy') }}
            </Button>
        </template>
    </Modal>
</template>

