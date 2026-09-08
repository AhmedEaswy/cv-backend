<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { TemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';

const { t } = useI18n();
const { user } = useAuthSession();
const portal = usePortalApi();
const toast = useToast();
const { profile, refresh: refreshProfile, setProfile } = usePortalPublicProfile();

const templates = ref<TemplateOption[]>([]);
const loading = ref(true);
const saving = ref(false);
const toggling = ref(false);

const form = reactive({
    name: '',
    headline: '',
    bio: '',
    email: '',
    phone: '',
    website: '',
    location: '',
    template_id: '' as string | number,
    is_public: true,
});

function applyProfile(p: typeof profile.value) {
    const ud = p?.user_data || {};
    const fullName = [ud.firstName, ud.lastName].filter(Boolean).join(' ').trim();
    form.name = fullName || user.value?.name || '';
    form.headline = p?.headline || '';
    form.bio = p?.about || '';
    form.email = ud.email || user.value?.email || '';
    form.phone = ud.phone || '';
    form.website = ud.website || '';
    form.location = ud.address || '';
    const templateId = p?.public_profile_template_id
        ?? templates.value.find((tpl) => tpl.is_default)?.id
        ?? '';
    // Only keep IDs that belong to public-profile templates (never CV templates).
    const valid = templates.value.some((tpl) => String(tpl.id) === String(templateId));
    form.template_id = valid ? templateId : (templates.value.find((tpl) => tpl.is_default)?.id ?? templates.value[0]?.id ?? '');
    form.is_public = p ? !!p.is_public : true;
}

function payload() {
    const parts = form.name.trim().split(/\s+/).filter(Boolean);
    const templateId = form.template_id
        && templates.value.some((tpl) => String(tpl.id) === String(form.template_id))
        ? form.template_id
        : null;
    return {
        headline: form.headline || null,
        about: form.bio || null,
        is_public: !!form.is_public,
        public_profile_template_id: templateId,
        user_data: {
            firstName: parts[0] || '',
            lastName: parts.slice(1).join(' ') || '',
            email: form.email || null,
            phone: form.phone || null,
            website: form.website || null,
            address: form.location || null,
        },
    };
}

onMounted(async () => {
    const [p, tpls] = await Promise.all([
        refreshProfile(),
        portal.list<TemplateOption>('/public-profiles/templates'),
    ]);
    templates.value = tpls;
    applyProfile(p);
    loading.value = false;
});

async function onSave() {
    saving.value = true;
    const body = payload();
    const updated = profile.value?.id
        ? await portal.update<NonNullable<typeof profile.value>>('/public-profiles', body, t('portal.public_profile.saved'))
        : await portal.create<NonNullable<typeof profile.value>>('/public-profiles', body);
    if (updated?.id) {
        setProfile(updated);
        applyProfile(updated);
    }
    saving.value = false;
}

async function onTogglePublic(value: boolean | number | string | null) {
    const next = !!value;
    form.is_public = next;
    if (!profile.value?.id) return;
    toggling.value = true;
    const updated = await portal.update<NonNullable<typeof profile.value>>('/public-profiles', {
        is_public: next,
    }, next ? t('portal.public_profile.published') : t('portal.public_profile.unpublished'));
    if (updated?.id) {
        setProfile(updated);
        form.is_public = !!updated.is_public;
    } else {
        form.is_public = !!profile.value.is_public;
    }
    toggling.value = false;
}

async function onCopyLink() {
    const url = profile.value?.public_url || (profile.value?.slug ? `${window.location.origin}/u/${profile.value.slug}` : '');
    if (!url) return;
    try {
        await navigator.clipboard.writeText(url);
        toast.success(t('portal.public_profile.link_copied'));
    } catch { /* ignore */ }
}

const previewUrl = computed(() => {
    if (profile.value?.public_url) return profile.value.public_url;
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
        <div v-if="profile?.id" class="page-header__actions page-header__actions--profile">
            <a v-if="profile.slug" :href="previewUrl" target="_blank" rel="noopener" class="btn btn--secondary">
                <Icon name="eye" :size="14" /> {{ t('portal.public_profile.preview') }}
            </a>
            <Button v-if="profile.slug" variant="secondary" @click="onCopyLink">
                <Icon name="copy" :size="14" /> {{ t('portal.public_profile.copy_link') }}
            </Button>
            <Switch
                :model-value="form.is_public"
                size="sm"
                :disabled="toggling || saving"
                @update:model-value="onTogglePublic"
            >
                <span>{{ t('portal.public_profile.field.public') }}</span>
            </Switch>
        </div>
    </header>

    <FormSkeleton v-if="loading" :fields="6" wide />

    <form v-else class="cv-builder" @submit.prevent="onSave">
        <div class="cv-builder__main">
            <div class="surface form-card">
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="name">{{ t('portal.public_profile.field.name') }}</label>
                        <input id="name" v-model="form.name" class="input" :placeholder="t('portal.public_profile.field.name_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="headline">{{ t('portal.public_profile.field.headline') }}</label>
                        <input id="headline" v-model="form.headline" class="input" :placeholder="t('portal.public_profile.field.headline_placeholder')" />
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="bio">{{ t('portal.public_profile.field.about') }}</label>
                    <textarea id="bio" v-model="form.bio" class="textarea" rows="4" :placeholder="t('portal.public_profile.field.about_placeholder')" />
                </div>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="email">{{ t('portal.public_profile.field.email') }}</label>
                        <input id="email" v-model="form.email" type="email" class="input" :placeholder="t('portal.public_profile.field.email_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="phone">{{ t('portal.public_profile.field.phone') }}</label>
                        <input id="phone" v-model="form.phone" type="tel" class="input" :placeholder="t('portal.public_profile.field.phone_placeholder')" />
                    </div>
                </div>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="website">{{ t('portal.public_profile.field.website') }}</label>
                        <input id="website" v-model="form.website" type="url" class="input" :placeholder="t('portal.public_profile.field.website_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="location">{{ t('portal.public_profile.field.location') }}</label>
                        <input id="location" v-model="form.location" class="input" :placeholder="t('portal.public_profile.field.location_placeholder')" />
                    </div>
                </div>
            </div>
        </div>

        <aside class="cv-builder__side">
            <div class="surface form-card cv-builder__meta">
                <div class="field">
                    <span class="field-label">{{ t('portal.public_profile.field.template') }}</span>
                    <CvTemplateSlider v-model="form.template_id" :templates="templates" kind="public-profile" />
                </div>
            </div>
        </aside>

        <div class="cv-builder__save">
            <Button type="submit" variant="primary" :loading="saving">
                {{ saving ? t('portal.public_profile.saving') : t('portal.public_profile.save') }}
            </Button>
        </div>
    </form>
</template>
