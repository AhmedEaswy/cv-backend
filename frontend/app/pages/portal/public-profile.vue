<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'portal' });
import type { TemplateOption } from '~/components/portal/cv/CvTemplateSlider.vue';
import type { PublicProfileSeo } from '~/composables/usePortalApi';
import {
    AlignLeftIcon,
    AtIcon,
    Briefcase01Icon,
    Globe02Icon,
    Image01Icon,
    Link01Icon,
    Location01Icon,
    Mail01Icon,
    Robot01Icon,
    Search01Icon,
    UserIcon,
} from '@hugeicons/core-free-icons';
import { copyToClipboard } from '~/utils/clipboard';

const { t, locale } = useI18n();
const route = useRoute();
const router = useRouter();
const { user } = useAuthSession();
const portal = usePortalApi();
const api = useApi();
const toast = useToast();
const { profile, refresh: refreshProfile, setProfile } = usePortalPublicProfile();
const { refresh: refreshStats } = usePortalStats();

const templates = ref<TemplateOption[]>([]);
const loading = ref(true);
const saving = ref(false);

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
    enable_inbox: true,
    profile_url_mode: 'slug' as 'slug' | 'subdomain' | 'custom_domain',
    enable_subdomain: false,
    custom_domain: '',
    enable_contact_form: false,
    contact_form_recipient: '',
    seo: {
        meta_title: '',
        meta_description: '',
        og_image: '',
        robots: 'index,follow',
    } as PublicProfileSeo,
    socialLinks: [] as SocialLinkCard[],
});

const pathUrl = ref('');
const subdomainUrl = ref('');
const customDomainUrl = ref('');
const customDomainDnsHost = ref('');
const customDomainDnsValue = ref('');
const customDomainVerified = ref(false);
const verifyingProfileDns = ref(false);
const copiedTarget = ref('');
let copiedTimer: ReturnType<typeof setTimeout> | undefined;

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
    form.enable_inbox = p?.enable_inbox !== false;
    form.profile_url_mode = (p?.profile_url_mode as typeof form.profile_url_mode) || (p?.enable_subdomain ? 'subdomain' : 'slug');
    form.enable_subdomain = form.profile_url_mode === 'subdomain';
    form.custom_domain = p?.custom_domain || '';
    form.enable_contact_form = !!p?.enable_contact_form;
    form.contact_form_recipient = p?.contact_form_recipient || '';
    const seo = (p?.user_data?.seo || {}) as PublicProfileSeo;
    form.seo.meta_title = seo.meta_title || '';
    form.seo.meta_description = seo.meta_description || '';
    form.seo.og_image = seo.og_image || '';
    form.seo.robots = seo.robots || 'index,follow';
    form.socialLinks = buildSocialLinkCards(p?.user_data?.socialLinks || []);
    pathUrl.value = p?.path_url || (p?.slug ? `/u/${p.slug}` : '');
    subdomainUrl.value = p?.subdomain_url || '';
    customDomainUrl.value = p?.custom_domain_url || '';
    customDomainDnsHost.value = p?.custom_domain_dns_host || '';
    customDomainDnsValue.value = p?.custom_domain_dns_value || '';
    customDomainVerified.value = !!p?.custom_domain_verified_at;
}

const urlModeOptions = computed(() => [
    { value: 'slug', label: t('portal.public_profile.url.mode_slug') },
    { value: 'subdomain', label: t('portal.public_profile.url.mode_subdomain') },
    { value: 'custom_domain', label: t('portal.public_profile.url.mode_custom_domain') },
]);

watch(() => form.profile_url_mode, (mode) => {
    form.enable_subdomain = mode === 'subdomain';
});

function payload() {
    const parts = form.name.trim().split(/\s+/).filter(Boolean);
    const templateId = form.template_id
        && templates.value.some((tpl) => String(tpl.id) === String(form.template_id))
        ? form.template_id
        : null;
    const socialLinks = socialLinksFromCards(form.socialLinks);

    return {
        headline: form.headline || null,
        about: form.bio || null,
        is_public: !!form.is_public,
        slug: form.slug.trim() || null,
        enable_inbox: !!form.enable_inbox,
        profile_url_mode: form.profile_url_mode,
        enable_subdomain: form.profile_url_mode === 'subdomain',
        custom_domain: form.profile_url_mode === 'custom_domain' ? (form.custom_domain.trim() || null) : null,
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
        await refreshStats();
    }
    saving.value = false;
}

function profileShareUrl() {
    return profile.value?.public_url || pathUrl.value || (profile.value?.slug ? `/u/${profile.value.slug}` : '');
}

function markCopied(target: string) {
    copiedTarget.value = target;
    if (copiedTimer) clearTimeout(copiedTimer);
    copiedTimer = setTimeout(() => {
        if (copiedTarget.value === target) copiedTarget.value = '';
    }, 2500);
}

async function copyAbsoluteUrl(url: string) {
    if (!url) return;
    const absolute = /^https?:\/\//i.test(url) ? url : `${window.location.origin}${url.startsWith('/') ? url : `/${url}`}`;
    try {
        await copyToClipboard(absolute);
        markCopied(url);
        toast.success(t('portal.public_profile.link_copied'));
    } catch {
        toast.error(t('portal.public_profile.link_copy_failed'));
    }
}

async function onCopyLink() {
    await copyAbsoluteUrl(profileShareUrl());
}

async function verifyCustomDomainDns() {
    verifyingProfileDns.value = true;
    try {
        const res = await api<{ result?: NonNullable<typeof profile.value>; message?: string }>(
            '/public-profiles/verify-custom-domain-dns',
            { method: 'POST' },
        );
        if (res?.result?.id) {
            setProfile(res.result);
            applyProfile(res.result);
        }
        toast.success(res?.message || t('portal.public_profile.url.custom_dns_verified'));
    } catch (e: any) {
        toast.error(e?.data?.message || t('portal.public_profile.url.custom_dns_pending'));
    } finally {
        verifyingProfileDns.value = false;
    }
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
                <Icon :name="copiedTarget === profileShareUrl() ? 'check' : 'copy'" :size="14" />
                {{ copiedTarget === profileShareUrl() ? t('portal.public_profile.link_copied') : t('portal.public_profile.copy_link') }}
            </Button>
        </div>
    </header>

    <FormSkeleton v-if="loading" :fields="6" wide />

    <form v-else class="cv-builder" @submit.prevent="onSave">
        <div class="cv-builder__main">
            <div class="surface form-card form-card--full">
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="name">{{ t('portal.public_profile.field.name') }}</label>
                        <FieldIcon :icon="UserIcon">
                            <input id="name" v-model="form.name" class="input" :placeholder="t('portal.public_profile.field.name_placeholder')" />
                        </FieldIcon>
                    </div>
                    <div class="field">
                        <label class="field-label" for="headline">{{ t('portal.public_profile.field.headline') }}</label>
                        <FieldIcon :icon="Briefcase01Icon">
                            <input id="headline" v-model="form.headline" class="input" :placeholder="t('portal.public_profile.field.headline_placeholder')" />
                        </FieldIcon>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="bio">{{ t('portal.public_profile.field.about') }}</label>
                    <FieldIcon :icon="AlignLeftIcon">
                        <textarea id="bio" v-model="form.bio" class="textarea" rows="4" :placeholder="t('portal.public_profile.field.about_placeholder')" />
                    </FieldIcon>
                </div>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="email">{{ t('portal.public_profile.field.email') }}</label>
                        <FieldIcon :icon="Mail01Icon">
                            <input id="email" v-model="form.email" type="email" class="input" :placeholder="t('portal.public_profile.field.email_placeholder')" />
                        </FieldIcon>
                    </div>
                    <div class="field">
                        <label class="field-label" for="phone">{{ t('portal.public_profile.field.phone') }}</label>
                        <PhoneInput
                            id="phone"
                            v-model="form.phone"
                            :placeholder="t('portal.public_profile.field.phone_placeholder')"
                        />
                    </div>
                </div>

                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="website">{{ t('portal.public_profile.field.website') }}</label>
                        <FieldIcon :icon="Globe02Icon">
                            <input id="website" v-model="form.website" type="url" class="input" :placeholder="t('portal.public_profile.field.website_placeholder')" />
                        </FieldIcon>
                    </div>
                    <div class="field">
                        <label class="field-label" for="location">{{ t('portal.public_profile.field.location') }}</label>
                        <FieldIcon :icon="Location01Icon">
                            <input id="location" v-model="form.location" class="input" :placeholder="t('portal.public_profile.field.location_placeholder')" />
                        </FieldIcon>
                    </div>
                </div>
            </div>

            <div class="surface form-card form-card--full">
                <h2 class="form-card__title">{{ t('portal.public_profile.contact.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.contact.subtitle') }}</p>
                <div class="field">
                    <Switch v-model="form.enable_inbox" size="sm">
                        <span>{{ t('portal.public_profile.inbox.enable') }}</span>
                    </Switch>
                    <span class="field-hint">{{ t('portal.public_profile.inbox.enable_help') }}</span>
                </div>
                <div class="field">
                    <Switch v-model="form.enable_contact_form" size="sm" :disabled="!form.enable_inbox">
                        <span>{{ t('portal.public_profile.contact.enable') }}</span>
                    </Switch>
                    <span class="field-hint">{{ t('portal.public_profile.contact.enable_help') }}</span>
                </div>
                <div class="field">
                    <label class="field-label" for="contact-recipient">{{ t('portal.public_profile.contact.recipient') }}</label>
                    <FieldIcon :icon="Mail01Icon">
                        <input
                            id="contact-recipient"
                            v-model="form.contact_form_recipient"
                            type="email"
                            class="input"
                            :placeholder="t('portal.public_profile.field.email_placeholder')"
                        />
                    </FieldIcon>
                    <span class="field-hint">{{ t('portal.public_profile.contact.recipient_help') }}</span>
                </div>
            </div>

            <div class="surface form-card form-card--full">
                <h2 class="form-card__title">{{ t('portal.public_profile.url.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.url.subtitle') }}</p>
                <div class="field">
                    <label class="field-label" for="slug">{{ t('portal.public_profile.field.slug') }}</label>
                    <FieldIcon :icon="AtIcon">
                        <input id="slug" v-model="form.slug" class="input" dir="ltr" :placeholder="t('portal.public_profile.field.slug_help')" />
                    </FieldIcon>
                    <span class="field-hint">{{ t('portal.public_profile.field.slug_help') }}</span>
                </div>
                <div class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.mode_label') }}</span>
                    <SelectInput v-model="form.profile_url_mode" :icon="Link01Icon" :options="urlModeOptions" />
                </div>
                <div v-if="form.profile_url_mode === 'custom_domain'" class="field">
                    <label class="field-label" for="custom-domain">{{ t('portal.public_profile.url.custom_domain_field') }}</label>
                    <FieldIcon :icon="Globe02Icon">
                        <input
                            id="custom-domain"
                            v-model="form.custom_domain"
                            class="input"
                            dir="ltr"
                            :placeholder="t('portal.public_profile.url.custom_domain_placeholder')"
                        />
                    </FieldIcon>
                </div>
                <div
                    v-if="form.profile_url_mode === 'custom_domain' && customDomainDnsHost && customDomainDnsValue"
                    class="dns-instructions"
                >
                    <h3 class="form-card__title form-card__title--sm">{{ t('portal.public_profile.url.custom_dns_title') }}</h3>
                    <p class="form-card__sub">{{ t('portal.public_profile.url.custom_dns_subtitle') }}</p>
                    <div class="dns-instructions__row">
                        <code class="dns-instructions__code">{{ customDomainDnsHost }}</code>
                        <Button type="button" variant="secondary" size="sm" :aria-label="t('portal.public_profile.url.copy')" @click="copyAbsoluteUrl(customDomainDnsHost)">
                            <Icon :name="copiedTarget === customDomainDnsHost ? 'check' : 'copy'" :size="14" />
                        </Button>
                    </div>
                    <div class="dns-instructions__row">
                        <code class="dns-instructions__code">{{ customDomainDnsValue }}</code>
                        <Button type="button" variant="secondary" size="sm" :aria-label="t('portal.public_profile.url.copy')" @click="copyAbsoluteUrl(customDomainDnsValue)">
                            <Icon :name="copiedTarget === customDomainDnsValue ? 'check' : 'copy'" :size="14" />
                        </Button>
                    </div>
                    <div class="dns-instructions__actions">
                        <Tag :variant="customDomainVerified ? 'success' : 'outline'">
                            {{ customDomainVerified ? t('portal.public_profile.url.custom_dns_verified') : t('portal.public_profile.url.custom_dns_pending') }}
                        </Tag>
                        <Button type="button" variant="secondary" :loading="verifyingProfileDns" @click="verifyCustomDomainDns">
                            {{ t('portal.public_profile.url.verify_custom_dns') }}
                        </Button>
                    </div>
                </div>
                <div v-if="pathUrl" class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.path') }}</span>
                    <div class="url-copy-row">
                        <code class="url-copy-row__text">{{ pathUrl }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyAbsoluteUrl(pathUrl)">
                            <Icon :name="copiedTarget === pathUrl ? 'check' : 'copy'" :size="14" />
                            {{ copiedTarget === pathUrl ? t('portal.public_profile.link_copied') : t('portal.public_profile.url.copy') }}
                        </Button>
                    </div>
                </div>
                <div v-if="subdomainUrl && form.profile_url_mode === 'subdomain'" class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.subdomain') }}</span>
                    <div class="url-copy-row">
                        <code class="url-copy-row__text">{{ subdomainUrl }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyAbsoluteUrl(subdomainUrl)">
                            <Icon :name="copiedTarget === subdomainUrl ? 'check' : 'copy'" :size="14" />
                            {{ copiedTarget === subdomainUrl ? t('portal.public_profile.link_copied') : t('portal.public_profile.url.copy') }}
                        </Button>
                    </div>
                </div>
                <div v-if="customDomainUrl && form.profile_url_mode === 'custom_domain'" class="field">
                    <span class="field-label">{{ t('portal.public_profile.url.custom_domain') }}</span>
                    <div class="url-copy-row">
                        <code class="url-copy-row__text">{{ customDomainUrl }}</code>
                        <Button type="button" variant="secondary" size="sm" @click="copyAbsoluteUrl(customDomainUrl)">
                            <Icon :name="copiedTarget === customDomainUrl ? 'check' : 'copy'" :size="14" />
                            {{ copiedTarget === customDomainUrl ? t('portal.public_profile.link_copied') : t('portal.public_profile.url.copy') }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="surface form-card form-card--full">
                <h2 class="form-card__title">{{ t('portal.public_profile.seo.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.seo.subtitle') }}</p>
                <div class="field">
                    <label class="field-label" for="seo-title">{{ t('portal.public_profile.seo.meta_title') }}</label>
                    <FieldIcon :icon="Search01Icon">
                        <input id="seo-title" v-model="form.seo.meta_title" class="input" :placeholder="t('portal.public_profile.seo.meta_title_placeholder')" />
                    </FieldIcon>
                </div>
                <div class="field">
                    <label class="field-label" for="seo-desc">{{ t('portal.public_profile.seo.meta_description') }}</label>
                    <FieldIcon :icon="AlignLeftIcon">
                        <textarea id="seo-desc" v-model="form.seo.meta_description" class="textarea" rows="3" :placeholder="t('portal.public_profile.seo.meta_description_placeholder')" />
                    </FieldIcon>
                </div>
                <div class="field-grid">
                    <div class="field">
                        <label class="field-label" for="seo-og">{{ t('portal.public_profile.seo.og_image') }}</label>
                        <FieldIcon :icon="Image01Icon">
                            <input id="seo-og" v-model="form.seo.og_image" class="input" dir="ltr" :placeholder="t('portal.public_profile.seo.og_image_placeholder')" />
                        </FieldIcon>
                    </div>
                    <div class="field">
                        <label class="field-label" for="seo-robots">{{ t('portal.public_profile.seo.robots') }}</label>
                        <SelectInput id="seo-robots" v-model="form.seo.robots" :icon="Robot01Icon" :options="robotsOptions" />
                    </div>
                </div>
            </div>

            <div class="surface form-card form-card--full">
                <h2 class="form-card__title">{{ t('portal.public_profile.social.title') }}</h2>
                <p class="form-card__sub">{{ t('portal.public_profile.social.hint') }}</p>
                <SocialLinksEditor v-model="form.socialLinks" />
            </div>
        </div>

        <aside class="cv-builder__side">
            <div class="surface form-card form-card--full cv-builder__meta">
                <div class="field">
                    <Switch v-model="form.is_public" :disabled="saving">
                        <span>{{ t('portal.public_profile.field.public') }}</span>
                    </Switch>
                </div>

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
