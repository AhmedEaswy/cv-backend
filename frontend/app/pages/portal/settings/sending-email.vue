<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { OutboundMailSettings } from '~/composables/usePortalApi';

const { t } = useI18n();
const api = useApi();
const toast = useToast();

const loading = ref(true);
const saving = ref(false);
const verifyingDns = ref(false);
const testing = ref(false);
const settings = ref<OutboundMailSettings | null>(null);

const form = reactive({
    domain: '',
    from_email: '',
    from_name: '',
    smtp_host: '',
    smtp_port: 587,
    smtp_encryption: 'tls',
    smtp_username: '',
    smtp_password: '',
    is_active: true,
});

function applySettings(data: OutboundMailSettings | null) {
    settings.value = data;
    if (!data) return;
    form.domain = data.domain || '';
    form.from_email = data.from_email || '';
    form.from_name = data.from_name || '';
    form.smtp_host = data.smtp_host || '';
    form.smtp_port = data.smtp_port || 587;
    form.smtp_encryption = data.smtp_encryption || 'tls';
    form.smtp_username = data.smtp_username || '';
    form.smtp_password = '';
    form.is_active = data.is_active !== false;
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

function buildBody() {
    const body: Record<string, unknown> = {
        domain: form.domain.trim() || null,
        from_email: form.from_email.trim() || null,
        from_name: form.from_name.trim() || null,
        smtp_host: form.smtp_host.trim() || null,
        smtp_port: form.smtp_port || null,
        smtp_encryption: form.smtp_encryption || null,
        smtp_username: form.smtp_username.trim() || null,
        is_active: form.is_active,
    };
    if (form.smtp_password.trim()) {
        body.smtp_password = form.smtp_password;
    }
    return body;
}

async function save() {
    saving.value = true;
    try {
        const res = await api<{ result?: OutboundMailSettings; message?: string }>('/settings/outbound-mail', {
            method: 'PUT',
            body: buildBody(),
        });
        applySettings(res?.result ?? null);
        toast.success(res?.message || t('portal.settings.sending_email.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not save');
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

async function sendTest() {
    testing.value = true;
    try {
        const res = await api<{ result?: OutboundMailSettings; message?: string }>('/settings/outbound-mail/test', {
            method: 'POST',
        });
        applySettings(res?.result ?? null);
        toast.success(res?.message || t('portal.settings.sending_email.test_sent'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.settings.sending_email.test_failed'));
    } finally {
        testing.value = false;
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
const smtpVerified = computed(() => !!settings.value?.smtp_verified_at);
const ready = computed(() => !!settings.value?.ready_for_sending);

const encryptionOptions = [
    { value: 'tls', label: 'TLS' },
    { value: 'ssl', label: 'SSL' },
    { value: '', label: 'None' },
];
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.nav.settings') }}</div>
            <h1 class="page-header__title">{{ t('portal.settings.sending_email.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.settings.sending_email.subtitle') }}</p>
        </div>
        <div v-if="!loading" class="page-header__actions">
            <Tag v-if="ready" variant="success">{{ t('portal.settings.sending_email.status_ready') }}</Tag>
            <Tag v-else variant="warning">{{ t('portal.settings.sending_email.status_incomplete') }}</Tag>
        </div>
    </header>

    <SettingsTabs />

    <FormSkeleton v-if="loading" :fields="8" wide />

    <form v-else class="cv-builder" @submit.prevent="save">
        <div class="cv-builder__main">
            <section class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.settings.sending_email.domain_title') }}</h2>
                <p class="form-card__sub">{{ t('portal.settings.sending_email.domain_subtitle') }}</p>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="out-domain">{{ t('portal.settings.sending_email.domain') }}</label>
                        <input id="out-domain" v-model="form.domain" class="input" dir="ltr" :placeholder="t('portal.settings.sending_email.domain_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="out-from-email">{{ t('portal.settings.sending_email.from_email') }}</label>
                        <input id="out-from-email" v-model="form.from_email" type="email" class="input" dir="ltr" />
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="out-from-name">{{ t('portal.settings.sending_email.from_name') }}</label>
                    <input id="out-from-name" v-model="form.from_name" class="input" />
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
            </section>

            <section class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.settings.sending_email.smtp_title') }}</h2>
                <p class="form-card__sub">{{ t('portal.settings.sending_email.smtp_subtitle') }}</p>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="out-smtp-host">{{ t('portal.settings.sending_email.smtp_host') }}</label>
                        <input id="out-smtp-host" v-model="form.smtp_host" class="input" dir="ltr" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="out-smtp-port">{{ t('portal.settings.sending_email.smtp_port') }}</label>
                        <input id="out-smtp-port" v-model.number="form.smtp_port" type="number" class="input" dir="ltr" />
                    </div>
                </div>
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="out-smtp-enc">{{ t('portal.settings.sending_email.smtp_encryption') }}</label>
                        <SelectInput
                            id="out-smtp-enc"
                            v-model="form.smtp_encryption"
                            :options="encryptionOptions"
                        />
                    </div>
                    <div class="field">
                        <label class="field-label" for="out-smtp-user">{{ t('portal.settings.sending_email.smtp_username') }}</label>
                        <input id="out-smtp-user" v-model="form.smtp_username" class="input" dir="ltr" autocomplete="off" />
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="out-smtp-pass">{{ t('portal.settings.sending_email.smtp_password') }}</label>
                    <input
                        id="out-smtp-pass"
                        v-model="form.smtp_password"
                        type="password"
                        class="input"
                        dir="ltr"
                        autocomplete="new-password"
                        :placeholder="settings?.has_smtp_password ? t('portal.settings.sending_email.smtp_password_set') : t('portal.settings.sending_email.smtp_password_placeholder')"
                    />
                </div>

                <div class="field">
                    <Switch v-model="form.is_active" size="sm">
                        <span>{{ t('portal.settings.sending_email.active') }}</span>
                    </Switch>
                </div>

                <div class="dns-instructions__actions">
                    <Tag :variant="smtpVerified ? 'success' : 'outline'">
                        {{ smtpVerified ? t('portal.settings.sending_email.smtp_verified') : t('portal.settings.sending_email.smtp_pending') }}
                    </Tag>
                    <Button type="button" variant="secondary" :loading="testing" @click="sendTest">
                        {{ t('portal.settings.sending_email.send_test') }}
                    </Button>
                </div>
            </section>
        </div>

        <div class="cv-builder__save">
            <Button type="submit" variant="primary" :loading="saving">
                {{ t('portal.settings.sending_email.save') }}
            </Button>
        </div>
    </form>
</template>
