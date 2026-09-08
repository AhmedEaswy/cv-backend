<script setup lang="ts">
/**
 * <Icon name="..." /> — central icon registry.
 * Keep names lowercase, hyphen-separated. Add new icons here as needed.
 */
defineProps<{ name: string; size?: number | string; class?: string }>();
</script>

<template>
    <svg
        :width="size ?? 18"
        :height="size ?? 18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.7"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        :class="$props.class"
    >
        <!-- Documents -->
        <template v-if="name === 'file'">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <path d="M14 2v6h6" />
            <path d="M9 13h6M9 17h6" />
        </template>
        <template v-else-if="name === 'file-plus'">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <path d="M14 2v6h6" />
            <path d="M12 18v-6M9 15h6" />
        </template>
        <template v-else-if="name === 'files'">
            <path d="M15 2H8a2 2 0 0 0-2 2v12" />
            <path d="M17 6H10a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V10z" />
        </template>
        <!-- Mail -->
        <template v-else-if="name === 'mail'">
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
        </template>
        <template v-else-if="name === 'inbox'">
            <path d="M22 12h-6l-2 3h-4l-2-3H2" />
            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
        </template>
        <!-- User -->
        <template v-else-if="name === 'user'">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </template>
        <template v-else-if="name === 'users'">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </template>
        <!-- Settings -->
        <template v-else-if="name === 'settings'">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
        </template>
        <template v-else-if="name === 'lock'">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </template>
        <!-- Layout / nav -->
        <template v-else-if="name === 'dashboard'">
            <rect x="3" y="3" width="7" height="7" rx="1.5" />
            <rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" />
            <rect x="14" y="14" width="7" height="7" rx="1.5" />
        </template>
        <template v-else-if="name === 'menu'">
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
        </template>
        <template v-else-if="name === 'close'">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
        </template>
        <template v-else-if="name === 'chevron-right'">
            <polyline points="9 18 15 12 9 6" />
        </template>
        <template v-else-if="name === 'chevron-left'">
            <polyline points="15 18 9 12 15 6" />
        </template>
        <template v-else-if="name === 'chevron-down'">
            <polyline points="6 9 12 15 18 9" />
        </template>
        <template v-else-if="name === 'chevron-up'">
            <polyline points="18 15 12 9 6 15" />
        </template>
        <template v-else-if="name === 'arrow-right'">
            <path d="M5 12h14M12 5l7 7-7 7" />
        </template>
        <template v-else-if="name === 'arrow-left'">
            <path d="M19 12H5M12 19l-7-7 7-7" />
        </template>
        <template v-else-if="name === 'external'">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
            <polyline points="15 3 21 3 21 9" />
            <line x1="10" y1="14" x2="21" y2="3" />
        </template>
        <!-- Actions -->
        <template v-else-if="name === 'grip'">
            <circle cx="9" cy="6" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="15" cy="6" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="9" cy="12" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="15" cy="12" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="9" cy="18" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="15" cy="18" r="1.15" fill="currentColor" stroke="none" />
        </template>
        <template v-else-if="name === 'to-top'">
            <polyline points="17 11 12 6 7 11" />
            <polyline points="17 18 12 13 7 18" />
        </template>
        <template v-else-if="name === 'to-bottom'">
            <polyline points="7 6 12 11 17 6" />
            <polyline points="7 13 12 18 17 13" />
        </template>
        <template v-else-if="name === 'plus'">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </template>
        <template v-else-if="name === 'trash'">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
        </template>
        <template v-else-if="name === 'copy'">
            <rect x="9" y="9" width="13" height="13" rx="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
        </template>
        <template v-else-if="name === 'edit'">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </template>
        <template v-else-if="name === 'eye'">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
            <circle cx="12" cy="12" r="3" />
        </template>
        <template v-else-if="name === 'download'">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" y1="15" x2="12" y2="3" />
        </template>
        <template v-else-if="name === 'more-vertical'">
            <circle cx="12" cy="5" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="12" cy="12" r="1.15" fill="currentColor" stroke="none" />
            <circle cx="12" cy="19" r="1.15" fill="currentColor" stroke="none" />
        </template>
        <!-- Status -->
        <template v-else-if="name === 'check'">
            <polyline points="20 6 9 17 4 12" />
        </template>
        <template v-else-if="name === 'check-circle'">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
        </template>
        <template v-else-if="name === 'alert'">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </template>
        <template v-else-if="name === 'x'">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
        </template>
        <template v-else-if="name === 'sparkles'">
            <path d="M12 3l1.9 5.7L19.5 10l-5.6 1.3L12 17l-1.9-5.7L4.5 10l5.6-1.3L12 3z" />
            <path d="M19 16l.7 2.1L22 19l-2.3.9L19 22l-.7-2.1L16 19l2.3-.9L19 16z" />
        </template>
        <template v-else-if="name === 'zap'">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
        </template>
        <template v-else-if="name === 'shield'">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
        </template>
        <template v-else-if="name === 'globe'">
            <circle cx="12" cy="12" r="10" />
            <line x1="2" y1="12" x2="22" y2="12" />
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
        </template>
        <template v-else-if="name === 'star'">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
        </template>
        <template v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
        </template>
        <template v-else-if="name === 'log-out'">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
        </template>
        <!-- ATS-specific -->
        <template v-else-if="name === 'target'">
            <circle cx="12" cy="12" r="10" />
            <circle cx="12" cy="12" r="6" />
            <circle cx="12" cy="12" r="2" />
        </template>
        <template v-else-if="name === 'briefcase'">
            <rect x="2" y="7" width="20" height="14" rx="2" />
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
        </template>
        <template v-else-if="name === 'google'">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
        </template>
        <!-- Fallback dot so missing icons still render something safe -->
        <template v-else>
            <circle cx="12" cy="12" r="2" fill="currentColor" />
        </template>
    </svg>
</template>
