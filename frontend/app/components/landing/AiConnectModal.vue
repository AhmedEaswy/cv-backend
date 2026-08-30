<script setup lang="ts">
const { t } = useI18n();
const { user } = await useAuth();
const { platforms, laravel } = useAiPlatforms();
const { open } = useAiConnectModal();
const track = useClickTracker();
const toast = useToast();

const tab = ref<'skill' | 'mcp'>('skill');
const copied = ref(false);
const copiedUrl = ref(false);
const skillText = ref('');

const mcpUrl = computed(() => `${laravel}/mcp/cv`);
const mcpConfig = computed(() => JSON.stringify({
    mcpServers: {
        cv: { url: mcpUrl.value },
    },
}, null, 2));

const loadSkill = async () => {
    if (skillText.value) return skillText.value;
    const origin = laravel || (import.meta.client ? window.location.origin : '');
    const res = await fetch(`${origin}/skill.md`);
    const body = await res.text();
    skillText.value = `Origin: ${origin}\n\n${body}`;
    return skillText.value;
};

const copySkill = async () => {
    const text = await loadSkill();
    await navigator.clipboard.writeText(text);
    copied.value = true;
    track('ai_connect_copy_skill', 'landing');
    toast.success(t('landing.ai_connect_copied'));
    window.setTimeout(() => { copied.value = false; }, 2000);
};

const copyMcpUrl = async () => {
    await navigator.clipboard.writeText(mcpUrl.value);
    copiedUrl.value = true;
    window.setTimeout(() => { copiedUrl.value = false; }, 2000);
};

const copyMcpConfig = async () => {
    await navigator.clipboard.writeText(mcpConfig.value);
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

<style scoped>
.ai-modal__lead { margin: 0 0 1rem; color: var(--color-ink-soft); line-height: 1.5; }
.ai-modal__tabs {
    display: flex;
    gap: 0.35rem;
    margin-bottom: 1rem;
    background: var(--color-paper-2);
    padding: 0.25rem;
    border-radius: 999px;
    width: fit-content;
}
.ai-modal__tabs button {
    border: 0;
    background: transparent;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    font-size: 0.85rem;
    cursor: pointer;
    color: var(--color-ink-soft);
}
.ai-modal__tabs button[aria-selected='true'] {
    background: var(--color-white);
    color: var(--color-ink);
    box-shadow: var(--shadow-1);
}
.ai-modal__steps {
    margin: 0 0 1rem;
    padding-inline-start: 1.2rem;
    color: var(--color-ink);
    font-size: 0.92rem;
    line-height: 1.7;
    list-style: decimal;
}
.ai-modal__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}
@media (max-width: 640px) {
    .ai-modal__grid { grid-template-columns: repeat(2, 1fr); }
}
.ai-modal__platform {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.7rem 0.4rem;
    border-radius: 0.75rem;
    border: 1px solid var(--color-line);
    background: linear-gradient(180deg, #fff, #f3f3f3);
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--color-ink);
}
.ai-modal__platform img { object-fit: contain; }
.ai-modal__platform:hover { border-color: var(--color-ink); }
.ai-modal__other {
    margin-top: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px dashed var(--color-line-2);
    background: transparent;
    border-radius: 999px;
    padding: 0.45rem 0.9rem;
    cursor: pointer;
}
.ai-modal__hint { color: var(--color-muted); font-size: 0.8rem; margin: 0.5rem 0 0; }
.ai-modal__mcp p { color: var(--color-ink-soft); line-height: 1.5; }
.ai-modal__label { display: block; font-size: 0.75rem; font-weight: 600; margin: 0.9rem 0 0.35rem; }
.ai-modal__code-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}
.ai-modal__code-row code {
    font-size: 0.8rem;
    background: var(--color-paper-2);
    padding: 0.35rem 0.55rem;
    border-radius: 0.4rem;
}
.ai-modal__pre {
    background: var(--color-paper-2);
    padding: 0.75rem;
    border-radius: 0.6rem;
    font-size: 0.75rem;
    overflow-x: auto;
}
</style>
