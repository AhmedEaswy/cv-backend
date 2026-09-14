<script setup lang="ts">
/**
 * /portal/cvs/create — create a blank CV (optionally with template_id) and open the editor.
 * Runs on the client so the Sanctum bearer token from localStorage is available.
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

onMounted(async () => {
    const created = await portal.createBlankCv(opts);
    await navigateTo(created?.id ? `/portal/cvs/${created.id}/edit` : '/portal/cvs', { replace: true });
});
</script>

<template>
    <div class="empty">
        <p class="empty__title">{{ $t('portal.cvs.creating') }}</p>
    </div>
</template>
