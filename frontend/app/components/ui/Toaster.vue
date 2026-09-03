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

