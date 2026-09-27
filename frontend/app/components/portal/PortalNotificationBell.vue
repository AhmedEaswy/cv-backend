<script setup lang="ts">
import type { PortalNotificationItem } from '~/composables/usePortalApi';

const { t } = useI18n();
const api = useApi();
const router = useRouter();
const { stats, refresh: refreshStats } = usePortalStats();

const open = ref(false);
const loading = ref(false);
const items = ref<PortalNotificationItem[]>([]);
const unreadCount = ref(0);
const rootEl = ref<HTMLElement | null>(null);
const triggerEl = ref<HTMLButtonElement | null>(null);
const menuEl = ref<HTMLElement | null>(null);
const menuStyle = ref<Record<string, string>>({});

const badgeCount = computed(() => {
    const fromStats = stats.value?.unread_notifications;
    if (typeof fromStats === 'number') return fromStats;
    return unreadCount.value;
});

function placeMenu() {
    const trigger = triggerEl.value;
    if (!trigger || !import.meta.client) return;
    const rect = trigger.getBoundingClientRect();
    const menuWidth = 320;
    const menuHeight = menuEl.value?.offsetHeight || 360;
    let left = rect.right - menuWidth;
    left = Math.min(Math.max(8, left), window.innerWidth - menuWidth - 8);
    let top = rect.bottom + 6;
    if (top + menuHeight > window.innerHeight - 8) {
        top = Math.max(8, rect.top - menuHeight - 6);
    }
    menuStyle.value = {
        position: 'fixed',
        top: `${Math.round(top)}px`,
        left: `${Math.round(left)}px`,
        zIndex: '1000',
        width: `${menuWidth}px`,
    };
}

async function loadNotifications() {
    loading.value = true;
    try {
        const res = await api<{
            result?: { data?: PortalNotificationItem[]; unread_count?: number };
        }>('/notifications', { query: { per_page: 10 } });
        const payload = res?.result;
        items.value = payload?.data ?? [];
        if (typeof payload?.unread_count === 'number') {
            unreadCount.value = payload.unread_count;
        }
    } catch {
        items.value = [];
    } finally {
        loading.value = false;
    }
}

async function toggle() {
    if (open.value) {
        open.value = false;
        return;
    }
    open.value = true;
    await loadNotifications();
    await nextTick();
    requestAnimationFrame(() => placeMenu());
}

function close() {
    open.value = false;
}

async function markAllRead() {
    try {
        await api('/notifications/read-all', { method: 'POST' });
        items.value = items.value.map((n) => ({ ...n, read_at: n.read_at || new Date().toISOString() }));
        unreadCount.value = 0;
        await refreshStats();
    } catch { /* ignore */ }
}

async function openNotification(n: PortalNotificationItem) {
    if (!n.read_at) {
        try {
            await api(`/notifications/${n.id}/read`, { method: 'POST' });
            n.read_at = new Date().toISOString();
            unreadCount.value = Math.max(0, unreadCount.value - 1);
            await refreshStats();
        } catch { /* ignore */ }
    }
    close();
    await router.push('/portal/inbox');
}

function onDocClick(e: MouseEvent) {
    if (!open.value || !rootEl.value) return;
    if (!rootEl.value.contains(e.target as Node)) close();
}

onMounted(() => {
    document.addEventListener('click', onDocClick);
    refreshStats();
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
});
</script>

<template>
    <div ref="rootEl" class="portal-notif">
        <button
            ref="triggerEl"
            type="button"
            class="portal-notif__trigger"
            :aria-expanded="open"
            :aria-label="t('portal.notifications.title')"
            @click.stop="toggle"
        >
            <Icon name="bell" :size="18" />
            <span v-if="badgeCount > 0" class="portal-notif__badge">{{ badgeCount > 9 ? '9+' : badgeCount }}</span>
        </button>

        <Teleport to="body">
            <div
                v-if="open"
                ref="menuEl"
                class="portal-notif__menu surface"
                :style="menuStyle"
                role="menu"
            >
                <div class="portal-notif__head">
                    <span class="portal-notif__title">{{ t('portal.notifications.title') }}</span>
                    <button
                        v-if="badgeCount > 0"
                        type="button"
                        class="portal-notif__mark-all"
                        @click="markAllRead"
                    >
                        {{ t('portal.notifications.mark_all_read') }}
                    </button>
                </div>

                <div v-if="loading" class="portal-notif__loading">{{ t('portal.notifications.loading') }}</div>
                <p v-else-if="items.length === 0" class="portal-notif__empty">{{ t('portal.notifications.empty') }}</p>
                <ul v-else class="portal-notif__list">
                    <li v-for="n in items" :key="n.id">
                        <button
                            type="button"
                            class="portal-notif__item"
                            :class="{ 'portal-notif__item--unread': !n.read_at }"
                            @click="openNotification(n)"
                        >
                            <span class="portal-notif__item-title">{{ n.title || t('portal.notifications.generic') }}</span>
                            <time v-if="n.created_at" class="portal-notif__item-time">
                                {{ new Date(n.created_at).toLocaleString() }}
                            </time>
                        </button>
                    </li>
                </ul>

                <NuxtLink to="/portal/inbox" class="portal-notif__foot" @click="close">
                    {{ t('portal.notifications.view_inbox') }}
                </NuxtLink>
            </div>
        </Teleport>
    </div>
</template>
