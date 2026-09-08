<script setup lang="ts">
/**
 * <Modal v-model:open="isOpen" title="...">
 * Renders only while open. Closes on Esc and backdrop click.
 */
const open = defineModel<boolean>('open', { default: false });

const props = withDefaults(defineProps<{
    title?: string;
    size?: 'sm' | 'md' | 'lg';
}>(), {
    size: 'md',
});

function close() {
    open.value = false;
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && open.value) close();
}

watch(open, (v) => {
    if (!import.meta.client) return;
    document.body.style.overflow = v ? 'hidden' : '';
});

onMounted(() => {
    if (import.meta.client) window.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
    if (import.meta.client) {
        window.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    }
});

const modalSizeClass = computed(() => {
    if (props.size === 'lg') return 'modal--lg';
    if (props.size === 'sm') return 'modal--sm';
    return 'modal--md';
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="modal-backdrop open"
            role="dialog"
            aria-modal="true"
            @click.self="close"
        >
            <div class="modal" :class="modalSizeClass">
                <header v-if="title || $slots.header" class="modal__header">
                    <slot name="header">
                        <h3 class="modal__title">{{ title }}</h3>
                    </slot>
                    <button type="button" class="btn btn--ghost btn--icon" aria-label="Close" @click="close">
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
