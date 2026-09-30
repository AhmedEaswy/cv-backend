<script setup lang="ts">
/**
 * <ConnectedAccounts> — list, link and unlink sign-in providers (Google / LinkedIn / Apple).
 * Linking is a full-page OAuth hop through Laravel that returns to /portal/settings
 * with `?linked=<provider>` or `?link_error=<reason>&provider=<provider>`.
 */
type ProviderId = 'google' | 'linkedin' | 'apple';

type ConnectedProvider = {
    provider: ProviderId;
    available: boolean;
    linked: boolean;
    linked_at: string | null;
    can_unlink: boolean;
};

const PROVIDER_NAMES: Record<ProviderId, string> = {
    google: 'Google',
    linkedin: 'LinkedIn',
    apple: 'Apple',
};

const { t, locale } = useI18n();
const api = useApi();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const providers = ref<ConnectedProvider[]>([]);
const loading = ref(true);
const busyProvider = ref<ProviderId | null>(null);

const visibleProviders = computed(() => providers.value.filter((item) => item.linked || item.available));

function providerName(provider: string): string {
    return PROVIDER_NAMES[provider as ProviderId] ?? provider;
}

function formatLinkedAt(value: string | null): string {
    if (!value) return '';
    return new Date(value).toLocaleDateString(locale.value, { year: 'numeric', month: 'short', day: 'numeric' });
}

async function load() {
    loading.value = true;
    try {
        const res = await api<{ result?: { providers?: ConnectedProvider[] } }>('/auth/social-accounts');
        providers.value = res?.result?.providers ?? [];
    } catch {
        providers.value = [];
    } finally {
        loading.value = false;
    }
}

async function link(item: ConnectedProvider) {
    busyProvider.value = item.provider;
    try {
        const res = await api<{ result?: { url?: string } }>(`/auth/social-accounts/${item.provider}/link`, { method: 'POST' });
        const url = res?.result?.url;
        if (!url) throw new Error('missing url');
        window.location.href = url;
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.connected_accounts.link_failed', { provider: providerName(item.provider) }));
        busyProvider.value = null;
    }
}

async function unlink(item: ConnectedProvider) {
    const name = providerName(item.provider);
    if (!window.confirm(t('portal.settings.connected_accounts.unlink_confirm', { provider: name }))) return;
    busyProvider.value = item.provider;
    try {
        await api(`/auth/social-accounts/${item.provider}`, { method: 'DELETE' });
        toast.success(t('portal.settings.connected_accounts.unlinked', { provider: name }));
        await load();
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.connected_accounts.unlink_failed', { provider: name }));
    } finally {
        busyProvider.value = null;
    }
}

function consumeLinkResult() {
    const { linked, link_error: linkError, provider, ...rest } = route.query;
    if (!linked && !linkError) return;

    if (typeof linked === 'string') {
        toast.success(t('portal.settings.connected_accounts.linked', { provider: providerName(linked) }));
    } else if (typeof linkError === 'string') {
        const name = providerName(typeof provider === 'string' ? provider : '');
        const errorKeys: Record<string, string> = {
            taken: 'portal.settings.connected_accounts.error_taken',
            already_linked: 'portal.settings.connected_accounts.error_already_linked',
        };
        toast.error(t(errorKeys[linkError] ?? 'portal.settings.connected_accounts.link_failed', { provider: name }));
    }

    router.replace({ query: rest });
}

onMounted(async () => {
    consumeLinkResult();
    await load();
});
</script>

<template>
    <section class="surface form-card form-card--xl connected-accounts">
        <h2 class="form-card__title">{{ t('portal.settings.connected_accounts.title') }}</h2>
        <p class="form-card__sub">{{ t('portal.settings.connected_accounts.subtitle') }}</p>

        <div v-if="loading" class="skeleton-stack" style="margin-top: 1rem">
            <div v-for="n in 3" :key="n" class="skeleton-list-card" style="padding: 0.85rem 1rem">
                <div class="skeleton-stack" style="flex: 1">
                    <Skeleton width="30%" height="0.85rem" />
                    <Skeleton width="20%" height="0.7rem" />
                </div>
                <Skeleton width="4.5rem" height="2rem" radius="9999px" />
            </div>
        </div>

        <ul v-else class="token-list">
            <li v-if="visibleProviders.length === 0" class="token-empty">
                {{ t('portal.settings.connected_accounts.empty') }}
            </li>
            <li v-for="item in visibleProviders" :key="item.provider" class="token-row">
                <div class="connected-accounts__info">
                    <SocialIcon :provider="item.provider" :size="24" />
                    <div>
                        <strong>{{ providerName(item.provider) }}</strong>
                        <p v-if="item.linked">
                            {{ t('portal.settings.connected_accounts.linked_on', { date: formatLinkedAt(item.linked_at) }) }}
                        </p>
                        <p v-else>{{ t('portal.settings.connected_accounts.not_linked') }}</p>
                    </div>
                </div>

                <div class="connected-accounts__actions">
                    <Tag v-if="item.linked" variant="success">{{ t('portal.settings.connected_accounts.status_linked') }}</Tag>
                    <Button
                        v-if="item.linked"
                        size="sm"
                        variant="danger"
                        :loading="busyProvider === item.provider"
                        :disabled="!item.can_unlink || (busyProvider !== null && busyProvider !== item.provider)"
                        :title="item.can_unlink ? undefined : t('portal.settings.connected_accounts.unlink_blocked')"
                        @click="unlink(item)"
                    >
                        {{ t('portal.settings.connected_accounts.unlink') }}
                    </Button>
                    <Button
                        v-else
                        size="sm"
                        variant="secondary"
                        :loading="busyProvider === item.provider"
                        :disabled="busyProvider !== null && busyProvider !== item.provider"
                        @click="link(item)"
                    >
                        {{ t('portal.settings.connected_accounts.link') }}
                    </Button>
                </div>
            </li>
        </ul>
    </section>
</template>
