<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { InboxMessage, InboxMessageReply, OutboundMailSettings } from '~/composables/usePortalApi';

const { t } = useI18n();
const api = useApi();
const toast = useToast();
const { refresh: refreshStats } = usePortalStats();

const messages = ref<InboxMessage[]>([]);
const loading = ref(true);
const filter = ref<'all' | 'unread' | 'read'>('all');
const selected = ref<InboxMessage | null>(null);
const replyBody = ref('');
const sendingReply = ref(false);
const reportingSpam = ref(false);
const retryingReplyId = ref<number | null>(null);
const outboundReady = ref(true);

function unwrapList<T>(res: { result?: T; data?: T } | null | undefined): T | null {
    if (!res) return null;
    if (res.result !== undefined) return res.result ?? null;
    if (res.data !== undefined) return res.data ?? null;
    return null;
}

async function loadOutbound() {
    try {
        const res = await api<{ result?: OutboundMailSettings }>('/settings/outbound-mail');
        const settings = res?.result;
        outboundReady.value = !!settings?.ready_for_sending;
    } catch {
        outboundReady.value = true;
    }
}

async function load() {
    loading.value = true;
    try {
        const res = await api<{ result?: InboxMessage[]; data?: InboxMessage[] }>('/public-profiles/inbox');
        messages.value = unwrapList(res) ?? [];
        if (selected.value) {
            selected.value = messages.value.find((m) => m.id === selected.value?.id) ?? null;
        }
    } catch {
        messages.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    await Promise.all([load(), loadOutbound()]);
});

async function markRead(m: InboxMessage) {
    if (m.is_read) return;
    try {
        const res = await api<{ result?: InboxMessage }>(`/public-profiles/inbox/${m.id}/read`, { method: 'POST' });
        const updated = res?.result;
        if (updated) {
            Object.assign(m, updated);
            if (selected.value?.id === m.id) selected.value = m;
        } else {
            m.is_read = true;
        }
        await refreshStats();
    } catch { /* ignore */ }
}

function open(m: InboxMessage) {
    selected.value = m;
    replyBody.value = '';
    markRead(m);
}

function deliveryTagVariant(status: string) {
    if (status === 'sent') return 'success';
    if (status === 'failed') return 'danger';
    if (status === 'queued') return 'soft';
    return 'outline';
}

function deliveryLabel(status: string) {
    const key = `portal.inbox.delivery.${status}`;
    const translated = t(key);
    return translated !== key ? translated : status;
}

async function sendReply() {
    const m = selected.value;
    const body = replyBody.value.trim();
    if (!m || !body) return;
    sendingReply.value = true;
    try {
        const res = await api<{ result?: InboxMessageReply; message?: string }>(
            `/public-profiles/inbox/${m.id}/reply`,
            { method: 'POST', body: { body } },
        );
        const reply = res?.result;
        if (reply) {
            if (!m.replies) m.replies = [];
            m.replies.push(reply);
            replyBody.value = '';
            toast.success(res?.message || t('portal.inbox.send_reply'));
        }
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not send reply');
    } finally {
        sendingReply.value = false;
    }
}

async function reportSpam() {
    const m = selected.value;
    if (!m || reportingSpam.value) return;
    if (!import.meta.client || !window.confirm(t('portal.inbox.report_spam_confirm'))) return;
    reportingSpam.value = true;
    try {
        await api(`/public-profiles/inbox/${m.id}/spam`, { method: 'POST' });
        toast.success(t('portal.inbox.spam_reported'));
        messages.value = messages.value.filter((row) => row.id !== m.id);
        selected.value = null;
        await refreshStats();
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not report spam');
    } finally {
        reportingSpam.value = false;
    }
}

async function retryReply(reply: InboxMessageReply) {
    const m = selected.value;
    if (!m || reply.delivery_status !== 'failed') return;
    retryingReplyId.value = reply.id;
    try {
        const res = await api<{ result?: InboxMessageReply; message?: string }>(
            `/public-profiles/inbox/${m.id}/replies/${reply.id}/retry`,
            { method: 'POST' },
        );
        const updated = res?.result;
        if (updated && m.replies) {
            const idx = m.replies.findIndex((r) => r.id === reply.id);
            if (idx >= 0) m.replies[idx] = updated;
        }
        toast.success(res?.message || t('portal.inbox.retry'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not retry');
    } finally {
        retryingReplyId.value = null;
    }
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
            <div class="page-header__eyebrow">{{ t('portal.inbox.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.inbox.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.inbox.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <div class="filter-pill">
                <button :class="['filter-pill__btn', filter === 'all' && 'active']" type="button" @click="filter = 'all'">
                    {{ t('portal.inbox.filter_all') }}
                </button>
                <button :class="['filter-pill__btn', filter === 'unread' && 'active']" type="button" @click="filter = 'unread'">
                    {{ t('portal.inbox.unread') }}
                </button>
                <button :class="['filter-pill__btn', filter === 'read' && 'active']" type="button" @click="filter = 'read'">
                    {{ t('portal.inbox.read') }}
                </button>
            </div>
        </div>
    </header>

    <div v-if="!outboundReady" class="portal-banner portal-banner--info">
        <Icon name="alert" :size="16" />
        <p>{{ t('portal.inbox.smtp_banner') }}</p>
        <NuxtLink to="/portal/settings/sending-email" class="portal-banner__link">
            {{ t('portal.inbox.smtp_banner_action') }}
        </NuxtLink>
    </div>

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
                type="button"
                @click="open(m)"
            >
                <div class="inbox-item__head">
                    <span class="inbox-item__name">{{ m.name || m.email || t('portal.inbox.anonymous') }}</span>
                    <span class="inbox-item__date">{{ m.created_at ? new Date(m.created_at).toLocaleDateString() : '' }}</span>
                </div>
                <div class="inbox-item__sub">{{ m.subject || m.message?.slice(0, 80) || t('portal.inbox.no_subject') }}</div>
            </button>
        </div>

        <article v-if="selected" class="inbox-detail surface">
            <header class="inbox-detail__head">
                <div>
                    <h2 class="inbox-detail__name">{{ selected.name || t('portal.inbox.anonymous') }}</h2>
                    <a v-if="selected.email" :href="`mailto:${selected.email}`" class="inbox-detail__email">{{ selected.email }}</a>
                </div>
                <Tag v-if="selected.is_read" variant="soft">{{ t('portal.inbox.read') }}</Tag>
                <Tag v-else variant="success">{{ t('portal.inbox.unread') }}</Tag>
            </header>
            <p class="inbox-detail__subject">{{ selected.subject || t('portal.inbox.no_subject') }}</p>
            <div class="inbox-detail__msg">{{ selected.message || '—' }}</div>

            <section v-if="selected.replies?.length" class="inbox-replies">
                <h3 class="inbox-replies__title">{{ t('portal.inbox.replies') }}</h3>
                <div v-for="reply in selected.replies" :key="reply.id" class="inbox-reply">
                    <div class="inbox-reply__meta">
                        <Tag :variant="deliveryTagVariant(reply.delivery_status)">
                            {{ deliveryLabel(reply.delivery_status) }}
                        </Tag>
                        <time v-if="reply.created_at" class="inbox-reply__time">
                            {{ new Date(reply.created_at).toLocaleString() }}
                        </time>
                        <Button
                            v-if="reply.delivery_status === 'failed'"
                            variant="secondary"
                            size="sm"
                            :loading="retryingReplyId === reply.id"
                            @click="retryReply(reply)"
                        >
                            {{ t('portal.inbox.retry') }}
                        </Button>
                    </div>
                    <p class="inbox-reply__body">{{ reply.body }}</p>
                    <p v-if="reply.error_message && reply.delivery_status === 'failed'" class="inbox-reply__error">
                        {{ reply.error_message }}
                    </p>
                </div>
            </section>

            <form class="inbox-compose" @submit.prevent="sendReply">
                <label class="field-label" for="inbox-reply">{{ t('portal.inbox.reply_body') }}</label>
                <textarea
                    id="inbox-reply"
                    v-model="replyBody"
                    class="textarea"
                    rows="4"
                    :placeholder="t('portal.inbox.reply_placeholder')"
                />
                <div class="inbox-detail__actions">
                    <Button type="submit" variant="primary" :loading="sendingReply" :disabled="!replyBody.trim()">
                        <Icon name="mail" :size="14" /> {{ sendingReply ? t('portal.inbox.sending') : t('portal.inbox.send_reply') }}
                    </Button>
                    <Button type="button" variant="secondary" :loading="reportingSpam" @click="reportSpam">
                        {{ t('portal.inbox.report_spam') }}
                    </Button>
                </div>
            </form>
        </article>
        <div v-else class="inbox-detail inbox-detail--empty surface">
            <Icon name="inbox" :size="28" />
            <p>{{ t('portal.inbox.view') }}</p>
        </div>
    </div>
</template>
