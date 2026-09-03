<script setup lang="ts">
/**
 * Shared prose shell for Privacy / Terms pages.
 */
defineProps<{
    title: string;
    updated: string;
}>();
</script>

<template>
    <article class="legal-doc">
        <header class="legal-doc__head">
            <h1 class="legal-doc__title">{{ title }}</h1>
            <p class="legal-doc__updated">{{ updated }}</p>
        </header>
        <div class="legal-doc__body">
            <slot />
        </div>
    </article>
</template>

