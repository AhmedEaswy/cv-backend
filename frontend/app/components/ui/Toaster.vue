<script setup lang="ts">
/**
 * <Toaster /> — mounts the global toast container. Drop once in app.vue.
 */
const { toasts, dismiss } = useToast();
</script>

<template>
    <div class="toaster" aria-live="polite" aria-atomic="true">
        <TransitionGroup name="toast">
            <div
                v-for="t in toasts"
                :key="t.id"
                :class="['toast', `toast--${t.variant}`]"
                role="status"
                @click="dismiss(t.id)"
            >
                <Icon
                    :name="t.variant === 'success' ? 'check-circle' : t.variant === 'error' ? 'alert' : 'sparkles'"
                    :size="18"
                />
                <span>{{ t.message }}</span>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toaster {
    position: fixed;
    bottom: 1.25rem;
    inset-inline-end: 1.25rem;
    z-index: 70;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    pointer-events: none;
    max-width: calc(100% - 2.5rem);
}
.toast {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    background: var(--color-white);
    color: var(--color-ink);
    border: 1px solid var(--color-line);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-3);
    font-size: 0.875rem;
    pointer-events: auto;
    cursor: pointer;
    max-width: 24rem;
}
.toast--success { border-inline-start: 3px solid var(--color-success); }
.toast--error   { border-inline-start: 3px solid var(--color-danger); }
.toast--info    { border-inline-start: 3px solid var(--color-ink); }

.toast-enter-active, .toast-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.toast-enter-from { opacity: 0; transform: translateY(8px); }
.toast-leave-to   { opacity: 0; transform: translateY(8px); }
</style>
