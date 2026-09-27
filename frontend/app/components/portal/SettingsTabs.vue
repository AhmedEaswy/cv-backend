<script setup lang="ts">
const { t } = useI18n();
const route = useRoute();

const current = computed(() => {
    if (route.path.includes('/ai-access')) return 'ai';
    if (route.path.includes('/notifications')) return 'notifications';
    if (route.path.includes('/sending-email')) return 'sending';
    if (route.query.tab === 'password') return 'password';
    return 'profile';
});
</script>

<template>
    <div class="tabs" role="tablist">
        <NuxtLink
            to="/portal/settings"
            role="tab"
            :custom="true"
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                role="tab"
                :aria-current="current === 'profile' ? 'page' : undefined"
                :class="{ 'is-active': current === 'profile' }"
                @click="navigate"
            >
                {{ t('portal.settings.profile.title') }}
            </a>
        </NuxtLink>
        <NuxtLink
            to="/portal/settings?tab=password"
            role="tab"
            :custom="true"
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                role="tab"
                :aria-current="current === 'password' ? 'page' : undefined"
                :class="{ 'is-active': current === 'password' }"
                @click="navigate"
            >
                {{ t('portal.settings.password.title') }}
            </a>
        </NuxtLink>
        <NuxtLink
            to="/portal/settings/notifications"
            role="tab"
            :custom="true"
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                role="tab"
                :aria-current="current === 'notifications' ? 'page' : undefined"
                :class="{ 'is-active': current === 'notifications' }"
                @click="navigate"
            >
                {{ t('portal.settings.notifications.title') }}
            </a>
        </NuxtLink>
        <NuxtLink
            to="/portal/settings/sending-email"
            role="tab"
            :custom="true"
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                role="tab"
                :aria-current="current === 'sending' ? 'page' : undefined"
                :class="{ 'is-active': current === 'sending' }"
                @click="navigate"
            >
                {{ t('portal.settings.sending_email.title') }}
            </a>
        </NuxtLink>
        <NuxtLink
            to="/portal/settings/ai-access"
            role="tab"
            :custom="true"
            v-slot="{ href, navigate }"
        >
            <a
                :href="href"
                role="tab"
                :aria-current="current === 'ai' ? 'page' : undefined"
                :class="{ 'is-active': current === 'ai' }"
                @click="navigate"
            >
                {{ t('portal.settings.ai.title') }}
            </a>
        </NuxtLink>
    </div>
</template>
