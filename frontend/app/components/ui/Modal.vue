<script setup lang="ts">
/**
 * <Modal v-model:open="isOpen" title="...">
 * Slot for body, default slot for footer. Closes on Esc and backdrop click.
 */
const props = defineProps<{
    open: boolean;
    title?: string;
    size?: 'sm' | 'md' | 'lg';
}>();
const emit = defineEmits<{ 'update:open': [value: boolean] }>();

function close() { emit('update:open', false); }

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && props.open) close();
}

if (import.meta.client) {
    watch(() => props.open, (v) => {
        document.body.style.overflow = v ? 'hidden' : '';
    });
    onMounted(() => window.addEventListener('keydown', onKeydown));
    onBeforeUnmount(() => {
        window.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    });
}

const sizeClass = computed(() => {
    if (props.size === 'lg') return 'max-w-2xl';
    if (props.size === 'sm') return 'max-w-md';
    return 'max-w-xl';
});
</script>

<template>
    <Teleport to="body">
        <div :class="['modal-backdrop', open && 'open']" @click.self="close" role="dialog" aria-modal="true">
            <div :class="['modal', sizeClass]">
                <header v-if="title || $slots.header" class="modal__header">
                    <slot name="header">
                        <h3 class="modal__title">{{ title }}</h3>
                    </slot>
                    <button type="button" class="btn btn--ghost btn--icon" @click="close" aria-label="Close">
                        <Icon name="close" :size="18" />
                    </button>
                </header>
                <div class="modal__body">
                    <slot />
                </div>
                <footer v-if="$slots.footer" class="modal__footer">
                    <slot name="footer" />
                </footer>
            </div>
        </div>
    </Teleport>
</template>
