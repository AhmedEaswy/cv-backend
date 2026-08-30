<script setup lang="ts">
/**
 * /portal/settings/ai-access — mint and revoke agent tokens for MCP.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const { t } = useI18n();
const api = useApi();
const toast = useToast();

type AgentToken = {
    id: number;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string | null;
};

const tokens = ref<AgentToken[]>([]);
const label = ref('claude-desktop');
const creating = ref(false);
const plainToken = ref('');
const loading = ref(true);

const load = async () => {
    loading.value = true;
    try {
        const data = await api('/agent-tokens');
        tokens.value = data?.result ?? [];
    } catch {
        tokens.value = [];
    } finally {
        loading.value = false;
    }
};

onMounted(load);

const createToken = async () => {
    creating.value = true;
    try {
        const data = await api('/agent-tokens', {
            method: 'POST',
            body: { name: label.value.trim() || 'agent' },
        });
        plainToken.value = data?.result?.token ?? '';
        toast.success(t('portal.settings.ai.created'));
        await load();
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
        </div>
    </header>

    <div class="tabs" role="tablist">
        <NuxtLink to="/portal/settings" role="tab">{{ t('portal.settings.profile.title') }}</NuxtLink>
        <NuxtLink to="/portal/settings" role="tab">{{ t('portal.settings.password.title') }}</NuxtLink>
        <span role="tab" aria-current="page">{{ t('portal.settings.ai.title') }}</span>
    </div>

    <section class="surface form-card">
        <h2 class="form-card__title">{{ t('portal.settings.ai.title') }}</h2>
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

        <ul class="token-list">
            <li v-if="!loading && tokens.length === 0" class="token-empty">
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

<style scoped>
.form-card { padding: 1.75rem; max-width: 48rem; }
.form-card__title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; margin: 0 0 0.25rem; }
.form-card__sub { color: var(--color-ink-soft); font-size: 0.9rem; margin: 0 0 1.5rem; }
.field { margin-bottom: 1rem; }
.token-form { display: flex; gap: 0.75rem; align-items: flex-end; flex-wrap: wrap; }
.token-form .field { flex: 1; min-width: 12rem; margin-bottom: 0; }
.token-once {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: var(--color-paper-2);
    border-radius: 0.75rem;
}
.token-once code { font-size: 0.78rem; word-break: break-all; }
.token-list { list-style: none; padding: 0; margin: 1.5rem 0 0; display: grid; gap: 0.6rem; }
.token-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem 0;
    border-top: 1px solid var(--color-line);
}
.token-row p { margin: 0.15rem 0 0; color: var(--color-muted); font-size: 0.8rem; }
.token-empty { color: var(--color-muted); }
.tabs a, .tabs span {
    display: inline-flex;
    padding: 0.45rem 0.9rem;
}
</style>
