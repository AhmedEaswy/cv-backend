<script setup lang="ts">
/**
 * <SocialIcon provider="google" :size="18" />
 * Brand artwork for sign-in providers, served by Laravel from /images/<provider>.svg.
 * Providers without artwork fall back to the inline <Icon>.
 */
const props = withDefaults(defineProps<{
    provider: string;
    size?: number;
}>(), { size: 18 });

const BRAND_IMAGES = new Set(['google', 'linkedin', 'apple']);

const config = useRuntimeConfig();
const laravel = String(config.public.laravelUrl || '').replace(/\/+$/, '');

const imageSrc = computed(() => (BRAND_IMAGES.has(props.provider) ? `${laravel}/images/${props.provider}.svg` : null));
</script>

<template>
    <img
        v-if="imageSrc"
        :src="imageSrc"
        alt=""
        aria-hidden="true"
        :width="size"
        :height="size"
        class="social-icon"
    >
    <Icon v-else :name="provider" :size="size" />
</template>
