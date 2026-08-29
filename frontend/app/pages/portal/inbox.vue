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
        // The inbox endpoint lives behind the public-profile inbox route.
        const res = await api<{ data: InboxMessage[] }>('/public-profiles/inbox');
        messages.value = res.data ?? [];
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

    <div v-if="loading" class="empty">
        <span class="empty__icon"><Icon name="clock" :size="22" /></span>
        <p class="empty__title">{{ t('portal.common.loading') }}</p>
    </div>

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

<style scoped>
.filter-pill {
    display: inline-flex;
    gap: 0.25rem;
    padding: 0.25rem;
    background: var(--color-paper-2);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-pill);
}
.filter-pill__btn {
    padding: 0.4rem 0.85rem;
    border-radius: var(--radius-pill);
    border: 0;
    background: transparent;
    color: var(--color-ink-soft);
    font-size: 0.825rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}
.filter-pill__btn.active { background: var(--color-ink); color: var(--color-paper); }

.inbox-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 1024px) { .inbox-layout { grid-template-columns: 22rem 1fr; } }

.inbox-list { display: flex; flex-direction: column; gap: 0.4rem; max-height: 60vh; overflow-y: auto; }
.inbox-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.85rem 1rem;
    background: var(--color-white);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    text-align: start;
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease;
    font-family: inherit;
    color: inherit;
}
.inbox-item:hover { border-color: var(--color-ink); }
.inbox-item--unread { border-inline-start: 3px solid var(--color-ink); }
.inbox-item--active { border-color: var(--color-ink); background: var(--color-paper-2); }
.inbox-item__head { display: flex; align-items: center; justify-content: space-between; }
.inbox-item__name { font-weight: 600; font-size: 0.9rem; color: var(--color-ink); }
.inbox-item__date { font-size: 0.75rem; color: var(--color-muted); }
.inbox-item__sub { font-size: 0.825rem; color: var(--color-ink-soft); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.inbox-detail { padding: 1.5rem 1.75rem; min-height: 60vh; }
.inbox-detail--empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; color: var(--color-ink-soft); }
.inbox-detail__head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
.inbox-detail__name { font-family: var(--font-display); font-size: 1.5rem; font-weight: 400; margin: 0 0 0.2rem; letter-spacing: -0.02em; }
.inbox-detail__email { color: var(--color-ink-soft); text-decoration: none; font-size: 0.875rem; }
.inbox-detail__email:hover { text-decoration: underline; }
.inbox-detail__subject { font-weight: 600; color: var(--color-ink); margin: 0 0 0.85rem; }
.inbox-detail__msg { color: var(--color-ink); line-height: 1.6; white-space: pre-wrap; }
.inbox-detail__actions { margin-top: 1.5rem; display: flex; gap: 0.5rem; }
</style>
