<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { OutboundMailSettings } from '~/composables/usePortalApi';
import { Globe02Icon, Mail01Icon, UserIcon } from '@hugeicons/core-free-icons';

const { t } = useI18n();
const api = useApi();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const verifyingDns = ref(false);
const settings = ref<OutboundMailSettings | null>(null);

const form = reactive({
    domain: '',
    from_email: '',
    from_name: '',
});

function applySettings(data: OutboundMailSettings | null) {
    settings.value = data;
    if (!data) return;
    form.domain = data.domain || '';
    form.from_email = data.from_email || '';
    form.from_name = data.from_name || '';
}

async function load() {
    loading.value = true;
    try {
        const res = await api<{ result?: OutboundMailSettings }>('/settings/outbound-mail');
        applySettings(res?.result ?? null);
    } finally {
        loading.value = false;
    }
}

onMounted(load);

async function save() {
    saving.value = true;
    try {
        const res = await api<{ result?: OutboundMailSettings; message?: string }>('/settings/outbound-mail', {
            method: 'PUT',
            body: {
                domain: form.domain.trim() || null,
                from_email: form.from_email.trim() || null,
                from_name: form.from_name.trim() || null,
            },
        });
        applySettings(res?.result ?? null);
        toast.success(res?.message || t('portal.settings.sending_email.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.sending_domain.save_failed'));
    } finally {
        saving.value = false;
    }
}

async function verifyDns() {
    verifyingDns.value = true;
    try {
        const res = await api<{ result?: OutboundMailSettings; message?: string }>('/settings/outbound-mail/verify-dns', {
            method: 'POST',
        });
        applySettings(res?.result ?? null);
        toast.success(res?.message || t('portal.settings.sending_email.dns_verified'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.sending_email.dns_failed'));
    } finally {
        verifyingDns.value = false;
    }
}

async function copyText(value: string) {
    if (!value) return;
    try {
        await navigator.clipboard.writeText(value);
        toast.success(t('portal.public_profile.link_copied'));
    } catch { /* ignore */ }
}

const dnsVerified = computed(() => !!settings.value?.dns_verified_at);
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.nav.settings') }}</div>
            <h1 class="page-header__title">{{ t('portal.settings.sending_domain.nav') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.settings.sending_domain.subtitle') }}</p>
        </div>
        <div v-if="!loading" class="page-header__actions">
            <Tag v-if="dnsVerified" variant="success">{{ t('portal.settings.sending_email.dns_verified') }}</Tag>
            <Tag v-else variant="warning">{{ t('portal.settings.sending_email.dns_pending') }}</Tag>
        </div>
    </header>

    <FormSkeleton v-if="loading" :fields="4" />

    <form v-else class="settings-cards" @submit.prevent="save">
        <section class="surface form-card form-card--xl">
                <h2 class="form-card__title">{{ t('portal.settings.sending_email.domain_title') }}</h2>
                <p class="form-card__sub">{{ t('portal.settings.sending_email.domain_subtitle') }}</p>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="out-domain">{{ t('portal.settings.sending_email.domain') }}</label>
                        <FieldIcon :icon="Globe02Icon">
                            <input id="out-domain" v-model="form.domain" class="input" dir="ltr" :placeholder="t('portal.settings.sending_email.domain_placeholder')" />
                        </FieldIcon>
                    </div>
                    <div class="field">
                        <label class="field-label" for="out-from-email">{{ t('portal.settings.sending_email.from_email') }}</label>
                        <FieldIcon :icon="Mail01Icon">
                            <input id="out-from-email" v-model="form.from_email" type="email" class="input" dir="ltr" />
                        </FieldIcon>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="out-from-name">{{ t('portal.settings.sending_email.from_name') }}</label>
                    <FieldIcon :icon="UserIcon">
                        <input id="out-from-name" v-model="form.from_name" class="input" />
                    </FieldIcon>
                </div>

                <div v-if="settings?.dns_txt_host && settings?.dns_txt_value" class="dns-instructions">
                    <p class="field-label">{{ t('portal.settings.sending_email.dns_instructions') }}</p>
                    <div class="dns-instructions__row">
                        <code class="dns-instructions__code">{{ settings.dns_txt_host }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyText(settings.dns_txt_host!)">
                            <Icon name="copy" :size="14" />
                        </Button>
                    </div>
                    <div class="dns-instructions__row">
                        <code class="dns-instructions__code">{{ settings.dns_txt_value }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyText(settings.dns_txt_value!)">
                            <Icon name="copy" :size="14" />
                        </Button>
                    </div>
                    <div class="dns-instructions__actions">
                        <Tag :variant="dnsVerified ? 'success' : 'outline'">
                            {{ dnsVerified ? t('portal.settings.sending_email.dns_verified') : t('portal.settings.sending_email.dns_pending') }}
                        </Tag>
                        <Button type="button" variant="secondary" :loading="verifyingDns" @click="verifyDns">
                            {{ t('portal.settings.sending_email.verify_dns') }}
                        </Button>
                    </div>
                </div>

                <div class="form-actions">
                    <Button type="submit" variant="primary" :loading="saving">
                        {{ t('portal.settings.sending_email.save') }}
                    </Button>
                </div>
            </section>
    </form>
</template>
