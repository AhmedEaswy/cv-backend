<script setup lang="ts">
/**
 * Leading icon inside a text field, matching the select and date inputs.
 */
import { HugeiconsIcon } from '@hugeicons/vue';
import { UserIcon } from '@hugeicons/core-free-icons';

defineProps<{
    icon: typeof UserIcon;
}>();
</script>

<template>
    <div class="ui-adorn">
        <span class="ui-adorn__icon" aria-hidden="true">
            <HugeiconsIcon :icon="icon" :size="16" :stroke-width="1.75" />
        </span>
        <slot />
    </div>
</template>
