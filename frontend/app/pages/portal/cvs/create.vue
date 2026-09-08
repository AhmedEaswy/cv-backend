<script setup lang="ts">
/**
 * /portal/cvs/create — create a blank CV (optionally with template_id) and open the editor.
 */
definePageMeta({ middleware: 'auth', layout: 'portal' });

const route = useRoute();
const portal = usePortalApi();

const rawTemplateId = Array.isArray(route.query.template_id)
    ? route.query.template_id[0]
    : route.query.template_id;
const templateId = Number(rawTemplateId);
const opts = Number.isFinite(templateId) && templateId > 0
    ? { template_id: templateId }
    : undefined;

const created = await portal.createBlankCv(opts);
await navigateTo(created?.id ? `/portal/cvs/${created.id}/edit` : '/portal/cvs');
</script>

<template>
    <div />
</template>
