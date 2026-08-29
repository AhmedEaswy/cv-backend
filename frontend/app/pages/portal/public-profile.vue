<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { ProfileData } from '~/composables/usePortalApi';

const { t } = useI18n();
const portal = usePortalApi();
const profile = ref<ProfileData | null>(null);
const templates = ref<Array<{ id: number; name: string }>>([]);
const loading = ref(true);
const saving = ref(false);
const publishing = ref(false);

const form = reactive<ProfileData>({
    name: '',
    headline: '',
    bio: '',
    avatar: '',
    email: '',
    phone: '',
    website: '',
    location: '',
    template_id: '' as any,
    color: '#0a0a0a',
});

onMounted(async () => {
    const [p, t1, t2] = await Promise.all([
        portal.show<ProfileData>('/public-profiles'),
        portal.list<any>('/public-profiles/templates').catch(() => []),
        portal.list<any>('/shares/templates').catch(() => []),
    ]);
    profile.value = p;
    templates.value = (t1 && t1.length ? t1 : t2) as any;
    Object.assign(form, p || {});
    loading.value = false;
});

async function onSave() {
    saving.value = true;
    const url = profile.value?.id ? '/public-profiles' : '/public-profiles';
    const method = profile.value?.id ? 'PUT' : 'POST';
    const api = useApi();
    const toast = useToast();
    try {
        const res = await api<{ data: ProfileData }>(url, { method, body: { ...form, template_id: form.template_id || null } });
        profile.value = res.data;
        Object.assign(form, res.data);
        toast.success(t('portal.public_profile.saved'));
    } catch (e: any) {
        toast.error(e?.data?.message || 'Could not save');
    } finally {
        saving.value = false;
    }
}

async function togglePublish() {
    if (!profile.value?.id) return;
    publishing.value = true;
    const updated = await portal.update('/public-profiles', {
        ...form,
        is_published: !profile.value.is_published,
    });
    if (updated) {
        profile.value = updated;
        Object.assign(form, updated);
    }
    publishing.value = false;
}

async function onCopyLink() {
    if (!profile.value?.slug) return;
    const url = `${window.location.origin}/u/${profile.value.slug}`;
    try {
        await navigator.clipboard.writeText(url);
        useToast().success(t('portal.public_profile.link_copied'));
    } catch { /* ignore */ }
}

const previewUrl = computed(() => {
    if (!profile.value?.slug) return '#';
    return `/u/${profile.value.slug}`;
});
</script>

<template>
    <header class="page-header">
        <div>
            <div class="page-header__eyebrow">{{ t('portal.public_profile.eyebrow') }}</div>
            <h1 class="page-header__title">{{ t('portal.public_profile.title') }}</h1>
            <p class="page-header__subtitle">{{ t('portal.public_profile.subtitle') }}</p>
        </div>
        <div class="page-header__actions">
            <a v-if="profile?.slug" :href="previewUrl" target="_blank" rel="noopener" class="btn btn--secondary">
                <Icon name="eye" :size="14" /> {{ t('portal.public_profile.preview') }}
            </a>
            <Button v-if="profile?.slug" variant="secondary" @click="onCopyLink">
                <Icon name="copy" :size="14" /> {{ t('portal.public_profile.copy_link') }}
            </Button>
            <Button v-if="profile?.id" :variant="profile.is_published ? 'secondary' : 'primary'" :loading="publishing" @click="togglePublish">
                {{ profile.is_published ? t('portal.public_profile.unpublish') : t('portal.public_profile.publish') }}
            </Button>
        </div>
    </header>

    <div v-if="loading" class="empty">
        <span class="empty__icon"><Icon name="clock" :size="22" /></span>
        <p class="empty__title">{{ t('portal.common.loading') }}</p>
    </div>

    <form v-else class="surface form-card" @submit.prevent="onSave">
        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="name">{{ t('portal.public_profile.field.name') }}</label>
                <input id="name" v-model="form.name" class="input" />
            </div>
            <div class="field">
                <label class="field-label" for="headline">{{ t('portal.public_profile.field.headline') }}</label>
                <input id="headline" v-model="form.headline" class="input" />
            </div>
        </div>

        <div class="field">
            <label class="field-label" for="bio">{{ t('portal.public_profile.field.bio') }}</label>
            <textarea id="bio" v-model="form.bio" class="textarea" rows="4" />
        </div>

        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="email">{{ t('portal.public_profile.field.email') }}</label>
                <input id="email" v-model="form.email" type="email" class="input" />
            </div>
            <div class="field">
                <label class="field-label" for="phone">{{ t('portal.public_profile.field.phone') }}</label>
                <input id="phone" v-model="form.phone" type="tel" class="input" />
            </div>
        </div>

        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="website">{{ t('portal.public_profile.field.website') }}</label>
                <input id="website" v-model="form.website" type="url" class="input" />
            </div>
            <div class="field">
                <label class="field-label" for="location">{{ t('portal.public_profile.field.location') }}</label>
                <input id="location" v-model="form.location" class="input" />
            </div>
        </div>

        <div class="field-grid">
            <div class="field">
                <label class="field-label" for="template">{{ t('portal.public_profile.field.template') }}</label>
                <select id="template" v-model="form.template_id" class="select">
                    <option value="">{{ t('portal.public_profile.field.template_none') }}</option>
                    <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">{{ tpl.name }}</option>
                </select>
            </div>
            <div class="field">
                <label class="field-label" for="color">{{ t('portal.public_profile.field.color') }}</label>
                <input id="color" v-model="form.color" type="color" class="input" style="height: 44px; padding: 4px;" />
            </div>
        </div>

        <div class="form-actions">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.common.loading') : t('portal.public_profile.save') }}
            </Button>
        </div>
    </form>
</template>

<style scoped>
.form-card { padding: 1.75rem; max-width: 56rem; }
.field { margin-bottom: 1rem; }
.field-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .field-grid { grid-template-columns: 1fr; } }
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }
</style>
