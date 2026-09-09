<script setup lang="ts">
/**
 * Skeleton grid matching LandingTemplateCard layout:
 * 3×N tiles with 3:4 preview + title/subtitle meta.
 */
withDefaults(defineProps<{ count?: number }>(), { count: 9 });
</script>

<template>
    <div
        class="tpl-grid templates-page__skeleton"
        role="status"
        aria-live="polite"
        aria-busy="true"
    >
        <article v-for="n in count" :key="n" class="tpl-tile tpl-tile--skeleton" aria-hidden="true">
            <div class="tpl-preview tpl-preview--media tpl-preview--skeleton">
                <div class="tpl-mock tpl-mock--skeleton">
                    <div class="tpl-mock__header">
                        <Skeleton circle height="24px" />
                        <div class="tpl-mock__title" style="gap: 0.35rem">
                            <Skeleton width="60%" height="6px" radius="2px" />
                            <Skeleton width="40%" height="5px" radius="2px" />
                        </div>
                    </div>
                    <Skeleton
                        v-for="i in 5"
                        :key="`a-${i}`"
                        :width="`${[95, 80, 90, 70, 60][i - 1]}%`"
                        height="4px"
                        radius="2px"
                    />
                    <Skeleton width="30%" height="1px" radius="0" class="tpl-mock__section-skel" />
                    <Skeleton
                        v-for="i in 4"
                        :key="`b-${i}`"
                        :width="`${[90, 85, 92, 75][i - 1]}%`"
                        height="4px"
                        radius="2px"
                    />
                </div>
            </div>
            <div class="tpl-meta tpl-meta--skeleton">
                <Skeleton width="55%" height="1.1rem" radius="6px" />
                <Skeleton width="88%" height="0.75rem" radius="4px" />
                <Skeleton width="70%" height="0.75rem" radius="4px" />
            </div>
        </article>
        <span class="sr-only">{{ $t('landing.templates_page.loading') }}</span>
    </div>
</template>
