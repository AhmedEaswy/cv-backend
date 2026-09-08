<script setup lang="ts">
/**
 * /portal/settings/ai-access — AI provider keys + MCP agent tokens.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const api = useApi();
const toast = useToast();

type ProviderModels = Record<string, Record<string, string>>;

type AiSettings = {
    provider: string;
    model: string | null;
    custom_url: string | null;
    base_url: string | null;
    has_api_key: boolean;
    providers: Array<{ value: string; label: string }>;
    provider_models: ProviderModels;
};

type AgentToken = {
    id: number;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string | null;
};

const settingsLoading = ref(true);
const savingSettings = ref(false);
const apiKeyInput = ref('');
const form = reactive({
    provider: 'openai',
    model: 'gpt-4o-mini',
    custom_url: '',
});
const hasApiKey = ref(false);
const providers = ref<Array<{ value: string; label: string }>>([
    { value: 'openai', label: 'OpenAI' },
    { value: 'openrouter', label: 'OpenRouter' },
    { value: 'custom', label: 'Custom' },
]);
const providerModels = ref<ProviderModels>({
    openai: {
        'gpt-4o-mini': 'GPT-4o mini',
        'gpt-4.1-mini': 'GPT-4.1 mini',
        'gpt-4.1': 'GPT-4.1',
    },
    openrouter: {
        'openai/gpt-4o-mini': 'OpenAI GPT-4o mini',
        'openai/gpt-4.1-mini': 'OpenAI GPT-4.1 mini',
        'anthropic/claude-3.5-sonnet': 'Claude 3.5 Sonnet',
    },
});

const tokens = ref<AgentToken[]>([]);
const label = ref('claude-desktop');
const creating = ref(false);
const plainToken = ref('');
const tokensLoading = ref(true);

const modelOptions = computed(() => {
    if (form.provider === 'custom') return [];
    return Object.entries(providerModels.value[form.provider] || {}).map(([value, name]) => ({ value, name }));
});

watch(() => form.provider, (provider) => {
    if (provider === 'custom') return;
    const options = Object.keys(providerModels.value[provider] || {});
    if (!options.length) {
        form.model = '';
        return;
    }
    if (!options.includes(form.model)) form.model = options[0] || '';
});

const loadSettings = async () => {
    settingsLoading.value = true;
    try {
        const data = await api<{ result: AiSettings }>('/ai-settings');
        const result = data?.result;
        if (!result) return;
        form.provider = result.provider || 'openai';
        form.model = result.model || '';
        form.custom_url = result.custom_url || '';
        hasApiKey.value = !!result.has_api_key;
        if (result.providers?.length) providers.value = result.providers;
        if (result.provider_models) providerModels.value = result.provider_models;
        apiKeyInput.value = '';
    } catch {
        /* keep defaults */
    } finally {
        settingsLoading.value = false;
    }
};

const loadTokens = async () => {
    tokensLoading.value = true;
    try {
        const data = await api('/agent-tokens');
        tokens.value = data?.result ?? [];
    } catch {
        tokens.value = [];
    } finally {
        tokensLoading.value = false;
    }
};

const initialLoading = ref(true);
onMounted(async () => {
    await Promise.all([loadSettings(), loadTokens()]);
    initialLoading.value = false;
});

const saveSettings = async () => {
    savingSettings.value = true;
    try {
        const body: Record<string, unknown> = {
            provider: form.provider,
            model: form.model,
            custom_url: form.provider === 'custom' ? form.custom_url : null,
        };
        if (apiKeyInput.value.trim()) body.api_key = apiKeyInput.value.trim();

        const data = await api<{ result: AiSettings; message?: string }>('/ai-settings', {
            method: 'PUT',
            body,
        });
        const result = data?.result;
        if (result) {
            hasApiKey.value = !!result.has_api_key;
            form.model = result.model || form.model;
            form.custom_url = result.custom_url || '';
        }
        apiKeyInput.value = '';
        toast.success(data?.message || t('portal.settings.ai.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.ai.save_failed'));
    } finally {
        savingSettings.value = false;
    }
};

const clearApiKey = async () => {
    savingSettings.value = true;
    try {
        const data = await api<{ result: AiSettings }>('/ai-settings', {
            method: 'PUT',
            body: {
                provider: form.provider,
                model: form.model,
                custom_url: form.provider === 'custom' ? form.custom_url : null,
                clear_api_key: true,
            },
        });
        hasApiKey.value = !!data?.result?.has_api_key;
        apiKeyInput.value = '';
        toast.success(t('portal.settings.ai.key_cleared'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.ai.save_failed'));
    } finally {
        savingSettings.value = false;
    }
};

const createToken = async () => {
    creating.value = true;
    try {
        const data = await api('/agent-tokens', {
            method: 'POST',
            body: { name: label.value.trim() || 'agent' },
        });
        plainToken.value = data?.result?.token ?? '';
        toast.success(t('portal.settings.ai.created'));
        await loadTokens();
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not create token');
    } finally {
        creating.value = false;
    }
};

const copyToken = async () => {
    if (!plainToken.value) return;
    await navigator.clipboard.writeText(plainToken.value);
    toast.success(t('portal.settings.ai.copied'));
};

const revoke = async (id: number) => {
    try {
        await api(`/agent-tokens/${id}`, { method: 'DELETE' });
        tokens.value = tokens.value.filter((token) => token.id !== id);
        toast.success(t('agent.token_revoked'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not revoke');
    }
};
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.nav.settings') }}</div>
            <h1 class="page-header__title">{{ t('portal.settings.ai.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.settings.ai.page_subtitle') }}</p>
        </div>
    </header>

    <SettingsTabs />

    <AiAccessSkeleton v-if="initialLoading" />

    <template v-else>
    <section class="surface form-card form-card--xl">
        <h2 class="form-card__title">{{ t('portal.settings.ai.provider_title') }}</h2>
        <p class="form-card__sub">{{ t('portal.settings.ai.provider_subtitle') }}</p>

        <form class="ai-settings-form" @submit.prevent="saveSettings">
            <div class="field">
                <label class="field-label" for="ai-api-key">{{ t('portal.settings.ai.api_key') }}</label>
                <input
                    id="ai-api-key"
                    v-model="apiKeyInput"
                    type="password"
                    class="input"
                    dir="ltr"
                    autocomplete="off"
                    :placeholder="hasApiKey ? t('portal.settings.ai.api_key_placeholder_set') : t('portal.settings.ai.api_key_placeholder')"
                />
                <span class="field-hint">
                    {{ hasApiKey ? t('portal.settings.ai.api_key_hint_set') : t('portal.settings.ai.api_key_hint') }}
                </span>
                <button
                    v-if="hasApiKey"
                    type="button"
                    class="btn btn--ghost btn--sm"
                    :disabled="savingSettings"
                    @click="clearApiKey"
                >
                    {{ t('portal.settings.ai.clear_key') }}
                </button>
            </div>

            <div class="field">
                <label class="field-label" for="ai-provider">{{ t('portal.settings.ai.provider') }}</label>
                <SelectInput
                    id="ai-provider"
                    v-model="form.provider"
                    :options="providers.map((p) => ({ value: p.value, label: p.label }))"
                />
            </div>

            <div v-if="form.provider === 'custom'" class="field">
                <label class="field-label" for="ai-custom-url">{{ t('portal.settings.ai.custom_url') }}</label>
                <input
                    id="ai-custom-url"
                    v-model="form.custom_url"
                    type="url"
                    class="input"
                    dir="ltr"
                    :placeholder="t('portal.settings.ai.custom_url_placeholder')"
                />
            </div>

            <div v-if="form.provider !== 'custom'" class="field">
                <label class="field-label" for="ai-model">{{ t('portal.settings.ai.model') }}</label>
                <SelectInput
                    id="ai-model"
                    v-model="form.model"
                    :options="modelOptions.map((opt) => ({ value: opt.value, label: opt.name }))"
                />
            </div>

            <div v-else class="field">
                <label class="field-label" for="ai-model-custom">{{ t('portal.settings.ai.model') }}</label>
                <input
                    id="ai-model-custom"
                    v-model="form.model"
                    type="text"
                    class="input"
                    dir="ltr"
                    :placeholder="t('portal.settings.ai.model_placeholder')"
                />
            </div>

            <div class="form-actions">
                <Button type="submit" variant="primary" :loading="savingSettings">
                    {{ t('portal.settings.ai.save') }}
                </Button>
            </div>
        </form>
    </section>

    <section class="surface form-card form-card--xl" style="margin-top: 1.25rem">
        <h2 class="form-card__title">{{ t('portal.settings.ai.tokens_title') }}</h2>
        <p class="form-card__sub">{{ t('portal.settings.ai.subtitle') }}</p>

        <div v-if="plainToken" class="token-once">
            <code>{{ plainToken }}</code>
            <Button size="sm" variant="secondary" @click="copyToken">{{ t('portal.settings.ai.copied') }}</Button>
        </div>

        <form class="token-form" @submit.prevent="createToken">
            <div class="field">
                <label class="field-label" for="token-name">{{ t('portal.settings.ai.name') }}</label>
                <input
                    id="token-name"
                    v-model="label"
                    class="input"
                    :placeholder="t('portal.settings.ai.name_placeholder')"
                    required
                />
            </div>
            <Button type="submit" variant="primary" :loading="creating">
                {{ t('portal.settings.ai.create') }}
            </Button>
        </form>

        <div v-if="tokensLoading" class="skeleton-stack" style="margin-top: 1rem">
            <div v-for="n in 2" :key="n" class="skeleton-list-card" style="padding: 0.85rem 1rem">
                <div class="skeleton-stack" style="flex: 1">
                    <Skeleton width="40%" height="0.85rem" />
                    <Skeleton width="25%" height="0.7rem" />
                </div>
                <Skeleton width="4.5rem" height="2rem" radius="9999px" />
            </div>
        </div>

        <ul v-else class="token-list">
            <li v-if="tokens.length === 0" class="token-empty">
                {{ t('portal.settings.ai.empty') }}
            </li>
            <li v-for="token in tokens" :key="token.id" class="token-row">
                <div>
                    <strong>{{ token.name }}</strong>
                    <p>{{ token.created_at }}</p>
                </div>
                <Button size="sm" variant="danger" @click="revoke(token.id)">
                    {{ t('portal.settings.ai.revoke') }}
                </Button>
            </li>
        </ul>
    </section>
    </template>
</template>
