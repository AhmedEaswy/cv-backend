<script setup lang="ts">
/**
 * /portal/cover-letters/create — create a blank cover letter and open the editor.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const route = useRoute();
const portal = usePortalApi();

const rawTemplateId = Array.isArray(route.query.cover_letter_template_id)
    ? route.query.cover_letter_template_id[0]
    : (route.query.cover_letter_template_id || route.query.template_id);
const templateId = Number(rawTemplateId);
const opts = Number.isFinite(templateId) && templateId > 0
    ? { cover_letter_template_id: templateId }
    : undefined;

const created = await portal.createBlankCoverLetter(opts);
await navigateTo(created?.id ? `/portal/cover-letters/${created.id}/edit` : '/portal/cover-letters');
</script>

<template>
    <div />
</template>
