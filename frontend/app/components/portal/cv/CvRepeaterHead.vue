<script setup lang="ts">
defineProps<{
    index: number;
    total: number;
}>();

const emit = defineEmits<{
    top: [];
    bottom: [];
    remove: [];
    dragstart: [event: DragEvent];
    dragend: [];
}>();
</script>

<template>
    <div class="cv-entry__head">
        <div class="cv-entry__sort">
            <span
                class="btn btn--ghost btn--sm btn--icon cv-entry__drag"
                role="button"
                tabindex="0"
                draggable="true"
                :aria-label="$t('portal.cvs.drag')"
                @dragstart="emit('dragstart', $event)"
                @dragend="emit('dragend')"
            >
                <Icon name="grip" :size="14" />
            </span>
            <button
                type="button"
                class="btn btn--ghost btn--sm btn--icon"
                :disabled="index === 0"
                :aria-label="$t('portal.cvs.move_to_top')"
                @click="emit('top')"
            >
                <Icon name="to-top" :size="14" />
            </button>
            <button
                type="button"
                class="btn btn--ghost btn--sm btn--icon"
                :disabled="index === total - 1"
                :aria-label="$t('portal.cvs.move_to_bottom')"
                @click="emit('bottom')"
            >
                <Icon name="to-bottom" :size="14" />
            </button>
        </div>
        <button
            type="button"
            class="btn btn--danger btn--sm btn--icon"
            :aria-label="$t('portal.cvs.remove')"
            @click="emit('remove')"
        >
            <Icon name="trash" :size="14" />
        </button>
    </div>
</template>
