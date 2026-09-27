<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { TemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';
import type { PublicProfileSeo, PublicProfileSocialLink, SocialLinkPlatform } from '~/composables/usePortalApi';

const SOCIAL_PLATFORMS: SocialLinkPlatform[] = [
    'linkedin', 'github', 'x', 'instagram', 'youtube', 'facebook', 'tiktok', 'snapchat',
    'calendly', 'behance', 'dribbble', 'medium', 'whatsapp', 'telegram', 'website', 'custom',
];

const { t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
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
    slug: '',
    enable_subdomain: false,
    enable_contact_form: false,
    contact_form_recipient: '',
    seo: {
        meta_title: '',
        meta_description: '',
        og_image: '',
        robots: 'index,follow',
    } as PublicProfileSeo,
    socialLinks: [] as PublicProfileSocialLink[],
});

const pathUrl = ref('');
const subdomainUrl = ref('');

const socialPlatformOptions = computed(() =>
    SOCIAL_PLATFORMS.map((platform) => ({
        value: platform,
        label: t(`portal.public_profile.social.platform.${platform}`),
    })),
);

const robotsOptions = computed(() => [
    { value: 'index,follow', label: t('portal.public_profile.seo.robots_default') },
    { value: 'noindex,nofollow', label: t('portal.public_profile.seo.robots_noindex') },
]);

function queryTemplateId(): number | null {
    const raw = Array.isArray(route.query.public_profile_template_id)
        ? route.query.public_profile_template_id[0]
        : (route.query.public_profile_template_id || route.query.template_id);
    const id = Number(raw);
    return Number.isFinite(id) && id > 0 ? id : null;
}

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
    const fromQuery = queryTemplateId();
    const templateId = (fromQuery
        && templates.value.some((tpl) => String(tpl.id) === String(fromQuery))
        ? fromQuery
        : null)
        ?? p?.public_profile_template_id
        ?? templates.value.find((tpl) => tpl.is_default)?.id
        ?? '';
    // Only keep IDs that belong to public-profile templates (never CV templates).
    const valid = templates.value.some((tpl) => String(tpl.id) === String(templateId));
    form.template_id = valid ? templateId : (templates.value.find((tpl) => tpl.is_default)?.id ?? templates.value[0]?.id ?? '');
    form.is_public = p ? !!p.is_public : true;
    form.slug = p?.slug || '';
    form.enable_subdomain = !!p?.enable_subdomain;
    form.enable_contact_form = !!p?.enable_contact_form;
    form.contact_form_recipient = p?.contact_form_recipient || '';
    const seo = (p?.user_data?.seo || {}) as PublicProfileSeo;
    form.seo.meta_title = seo.meta_title || '';
    form.seo.meta_description = seo.meta_description || '';
    form.seo.og_image = seo.og_image || '';
    form.seo.robots = seo.robots || 'index,follow';
    form.socialLinks = (p?.user_data?.socialLinks || []).map((link) => ({
        platform: link.platform || 'website',
        url: link.url || '',
        label: link.label || '',
    }));
    pathUrl.value = p?.path_url || (p?.slug ? `/u/${p.slug}` : '');
    subdomainUrl.value = p?.subdomain_url || '';
}

function addSocialLink() {
    form.socialLinks.push({ platform: 'linkedin', url: '', label: '' });
}

function removeSocialLink(index: number) {
    form.socialLinks.splice(index, 1);
}

function payload() {
    const parts = form.name.trim().split(/\s+/).filter(Boolean);
    const templateId = form.template_id
        && templates.value.some((tpl) => String(tpl.id) === String(form.template_id))
        ? form.template_id
        : null;
    const socialLinks = form.socialLinks
        .map((link) => ({
            platform: link.platform,
            url: link.url?.trim() || '',
            ...(link.platform === 'custom' && link.label?.trim() ? { label: link.label.trim() } : {}),
        }))
        .filter((link) => link.url);

    return {
        headline: form.headline || null,
        about: form.bio || null,
        is_public: !!form.is_public,
        slug: form.slug.trim() || null,
        enable_subdomain: !!form.enable_subdomain,
        enable_contact_form: !!form.enable_contact_form,
        contact_form_recipient: form.contact_form_recipient.trim() || null,
        public_profile_template_id: templateId,
        user_data: {
            firstName: parts[0] || '',
            lastName: parts.slice(1).join(' ') || '',
            email: form.email || null,
            phone: form.phone || null,
            website: form.website || null,
            address: form.location || null,
            seo: {
                meta_title: form.seo.meta_title?.trim() || null,
                meta_description: form.seo.meta_description?.trim() || null,
                og_image: form.seo.og_image?.trim() || null,
                robots: form.seo.robots?.trim() || null,
            },
            socialLinks,
        },
    };
}

async function loadTemplates(lang?: string) {
    const previewLocale = lang || profile.value?.language || locale.value;
    templates.value = await portal.list<TemplateOption>('/public-profiles/templates', {
        locale: previewLocale,
    });
}

onMounted(async () => {
    const p = await refreshProfile();
    await loadTemplates(p?.language || locale.value);
    applyProfile(p);
    loading.value = false;

    // Clear gallery deep-link once applied so refresh keeps the saved template.
    if (queryTemplateId() && (route.query.public_profile_template_id || route.query.template_id)) {
        const { public_profile_template_id: _a, template_id: _b, ...rest } = route.query;
        await router.replace({ query: rest });
    }
});

watch(locale, async () => {
    const selected = form.template_id;
    await loadTemplates(profile.value?.language || locale.value);
    if (selected && templates.value.some((tpl) => String(tpl.id) === String(selected))) {
        form.template_id = selected;
    }
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

async function copyAbsoluteUrl(url: string) {
    if (!url) return;
    const absolute = /^https?:\/\//i.test(url) ? url : `${window.location.origin}${url.startsWith('/') ? url : `/${url}`}`;
    try {
        await navigator.clipboard.writeText(absolute);
        toast.success(t('portal.public_profile.link_copied'));
    } catch { /* ignore */ }
}

async function onCopyLink() {
    const url = profile.value?.public_url || pathUrl.value || (profile.value?.slug ? `/u/${profile.value.slug}` : '');
    await copyAbsoluteUrl(url);
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

            <div class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.public_profile.contact.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.contact.subtitle') }}</p>
                <div class="field">
                    <Switch v-model="form.enable_contact_form" size="sm">
                        <span>{{ t('portal.public_profile.contact.enable') }}</span>
                    </Switch>
                    <span class="field-hint">{{ t('portal.public_profile.contact.enable_help') }}</span>
                </div>
                <div class="field">
                    <label class="field-label" for="contact-recipient">{{ t('portal.public_profile.contact.recipient') }}</label>
                    <input
                        id="contact-recipient"
                        v-model="form.contact_form_recipient"
                        type="email"
                        class="input"
                        :placeholder="t('portal.public_profile.field.email_placeholder')"
                    />
                    <span class="field-hint">{{ t('portal.public_profile.contact.recipient_help') }}</span>
                </div>
            </div>

            <div class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.public_profile.url.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.url.subtitle') }}</p>
                <div class="field">
                    <label class="field-label" for="slug">{{ t('portal.public_profile.field.slug') }}</label>
                    <input id="slug" v-model="form.slug" class="input" dir="ltr" :placeholder="t('portal.public_profile.field.slug_help')" />
                    <span class="field-hint">{{ t('portal.public_profile.field.slug_help') }}</span>
                </div>
                <div class="field">
                    <Switch v-model="form.enable_subdomain" size="sm">
                        <span>{{ t('portal.public_profile.url.enable_subdomain') }}</span>
                    </Switch>
                    <span class="field-hint">{{ t('portal.public_profile.url.enable_subdomain_help') }}</span>
                </div>
                <div v-if="pathUrl" class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.path') }}</span>
                    <div class="url-copy-row">
                        <code class="url-copy-row__text">{{ pathUrl }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyAbsoluteUrl(pathUrl)">
                            <Icon name="copy" :size="14" /> {{ t('portal.public_profile.url.copy') }}
                        </Button>
                    </div>
                </div>
                <div v-if="subdomainUrl" class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.subdomain') }}</span>
                    <div class="url-copy-row">
                        <code class="url-copy-row__text">{{ subdomainUrl }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyAbsoluteUrl(subdomainUrl)">
                            <Icon name="copy" :size="14" /> {{ t('portal.public_profile.url.copy') }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.public_profile.seo.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.seo.subtitle') }}</p>
                <div class="field">
                    <label class="field-label" for="seo-title">{{ t('portal.public_profile.seo.meta_title') }}</label>
                    <input id="seo-title" v-model="form.seo.meta_title" class="input" :placeholder="t('portal.public_profile.seo.meta_title_placeholder')" />
                </div>
                <div class="field">
                    <label class="field-label" for="seo-desc">{{ t('portal.public_profile.seo.meta_description') }}</label>
                    <textarea id="seo-desc" v-model="form.seo.meta_description" class="textarea" rows="3" :placeholder="t('portal.public_profile.seo.meta_description_placeholder')" />
                </div>
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="seo-og">{{ t('portal.public_profile.seo.og_image') }}</label>
                        <input id="seo-og" v-model="form.seo.og_image" class="input" dir="ltr" :placeholder="t('portal.public_profile.seo.og_image_placeholder')" />
                    </div>
                    <div class="field">
                        <label class="field-label" for="seo-robots">{{ t('portal.public_profile.seo.robots') }}</label>
                        <SelectInput id="seo-robots" v-model="form.seo.robots" :options="robotsOptions" />
                    </div>
                </div>
            </div>

            <div class="surface form-card">
                <h2 class="form-card__title">{{ t('portal.public_profile.social.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.social.subtitle') }}</p>
                <p v-if="form.socialLinks.length === 0" class="field-hint">{{ t('portal.public_profile.social.empty') }}</p>
                <article v-for="(link, index) in form.socialLinks" :key="index" class="cv-entry">
                    <div class="cv-entry__head">
                        <span class="field-label">{{ t('portal.public_profile.social.platform') }} #{{ index + 1 }}</span>
                        <button type="button" class="btn btn--ghost btn--sm" @click="removeSocialLink(index)">
                            <Icon name="trash" :size="14" /> {{ t('portal.public_profile.social.remove') }}
                        </button>
                    </div>
                    <div class="field-grid">
                        <div class="field" style="margin-bottom: 0">
                            <label class="field-label">{{ t('portal.public_profile.social.platform') }}</label>
                            <SelectInput v-model="link.platform" :options="socialPlatformOptions" />
                        </div>
                        <div class="field" style="margin-bottom: 0">
                            <label class="field-label">{{ t('portal.public_profile.social.url') }}</label>
                            <input v-model="link.url" type="url" class="input" dir="ltr" />
                        </div>
                    </div>
                    <div v-if="link.platform === 'custom'" class="field" style="margin-top: 0.75rem; margin-bottom: 0">
                        <label class="field-label">{{ t('portal.public_profile.social.label') }}</label>
                        <input v-model="link.label" class="input" :placeholder="t('portal.public_profile.social.label_placeholder')" />
                    </div>
                </article>
                <button type="button" class="btn btn--secondary btn--sm" @click="addSocialLink">
                    <Icon name="plus" :size="14" /> {{ t('portal.public_profile.social.add') }}
                </button>
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
