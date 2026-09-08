<script setup lang="ts">
const { t } = useI18n();
const route = useRoute();

const current = computed(() => {
    if (route.path.includes('/ai-access')) return 'ai';
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
