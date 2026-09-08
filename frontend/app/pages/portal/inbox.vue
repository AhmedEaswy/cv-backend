<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { InboxMessage } from '~/composables/usePortalApi';

const { t } = useI18n();
const api = useApi();
const messages = ref<InboxMessage[]>([]);
const loading = ref(true);
const filter = ref<'all' | 'unread' | 'read'>('all');
const selected = ref<InboxMessage | null>(null);

async function load() {
    loading.value = true;
    try {
        const res = await api<{ result?: InboxMessage[]; data?: InboxMessage[] }>('/public-profiles/inbox');
        messages.value = res.result ?? res.data ?? [];
    } catch {
        messages.value = [];
    } finally {
        loading.value = false;
    }
}
onMounted(load);

async function markRead(m: InboxMessage) {
    if (m.is_read) return;
    try {
        await api(`/public-profiles/inbox/${m.id}/read`, { method: 'POST' });
        m.is_read = true;
    } catch { /* ignore */ }
}
function open(m: InboxMessage) {
    selected.value = m;
    markRead(m);
}
function replyTo(m: InboxMessage) {
    if (m.email) window.location.href = `mailto:${m.email}`;
}
const filtered = computed(() => {
    if (filter.value === 'unread') return messages.value.filter((m) => !m.is_read);
    if (filter.value === 'read') return messages.value.filter((m) => m.is_read);
    return messages.value;
});
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">Inbox</div>
            <h1 class="page-header__title">{{ t('portal.inbox.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.inbox.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <div class="filter-pill">
                <button :class="['filter-pill__btn', filter === 'all' && 'active']" @click="filter = 'all'">All</button>
                <button :class="['filter-pill__btn', filter === 'unread' && 'active']" @click="filter = 'unread'">{{ t('portal.inbox.unread') }}</button>
                <button :class="['filter-pill__btn', filter === 'read' && 'active']" @click="filter = 'read'">{{ t('portal.inbox.read') }}</button>
            </div>
        </div>
    </header>

    <InboxSkeleton v-if="loading" />

    <div v-else-if="messages.length === 0" class="empty">
        <span class="empty__icon"><Icon name="inbox" :size="22" /></span>
        <p class="empty__title">{{ t('portal.inbox.empty') }}</p>
        <p class="empty__text">{{ t('portal.inbox.empty_text') }}</p>
    </div>

    <div v-else class="inbox-layout">
        <div class="inbox-list">
            <button
                v-for="m in filtered"
                :key="m.id"
                :class="['inbox-item', !m.is_read && 'inbox-item--unread', selected?.id === m.id && 'inbox-item--active']"
                @click="open(m)"
                type="button"
            >
                <div class="inbox-item__head">
                    <span class="inbox-item__name">{{ m.name || m.email || 'Anonymous' }}</span>
                    <span class="inbox-item__date">{{ m.created_at ? new Date(m.created_at).toLocaleDateString() : '' }}</span>
                </div>
                <div class="inbox-item__sub">{{ m.subject || m.message?.slice(0, 80) || '—' }}</div>
            </button>
        </div>

        <article v-if="selected" class="inbox-detail surface">
            <header class="inbox-detail__head">
                <div>
                    <h2 class="inbox-detail__name">{{ selected.name || 'Anonymous' }}</h2>
                    <a :href="`mailto:${selected.email}`" class="inbox-detail__email">{{ selected.email }}</a>
                </div>
                <Tag v-if="selected.is_read" variant="soft">{{ t('portal.inbox.read') }}</Tag>
                <Tag v-else variant="success">{{ t('portal.inbox.unread') }}</Tag>
            </header>
            <p class="inbox-detail__subject">{{ selected.subject || '—' }}</p>
            <div class="inbox-detail__msg">{{ selected.message || '—' }}</div>
            <div class="inbox-detail__actions">
                <Button variant="primary" @click="replyTo(selected)">
                    <Icon name="mail" :size="14" /> {{ t('portal.inbox.reply') }}
                </Button>
            </div>
        </article>
        <div v-else class="inbox-detail inbox-detail--empty surface">
            <Icon name="inbox" :size="28" />
            <p>{{ t('portal.inbox.view') }}</p>
        </div>
    </div>
</template>

