<script setup lang="ts">
defineProps<{ variant?: 'success' | 'error' | 'info' }>();
</script>

<template>
    <div :class="['alert', `alert--${variant ?? 'info'}`]" role="alert">
        <Icon :name="variant === 'success' ? 'check-circle' : variant === 'error' ? 'alert' : 'sparkles'" :size="18" />
        <div><slot /></div>
    </div>
</template>
