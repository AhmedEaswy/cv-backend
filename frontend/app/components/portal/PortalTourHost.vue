<script setup lang="ts">
import { portalTourBlockedForRoute, usePortalTour } from '~/composables/usePortalTour';

const { t } = useI18n();
const route = useRoute();
const tour = usePortalTour();

const spotlightStyle = ref<Record<string, string>>({});
const cardStyle = ref<Record<string, string>>({});

const tourRunning = computed(() => tour.isTourRunning());

function offerTourForPath(path: string) {
    if (!path.startsWith('/portal')) return;
    if (path === '/portal') {
        void tour.tryOfferTour(path, 'dashboard');
        void tour.tryOfferTour(path, 'visit');
        return;
    }
    void tour.tryOfferTour(path, 'visit');
}

watch(
    () => route.path,
    (path) => offerTourForPath(path),
    { immediate: true },
);

function positionSpotlight() {
    if (!import.meta.client || !tourRunning.value || !tour.currentStep.value) return;
    const target = tour.stepTarget(tour.stepIndex.value);
    const el = document.querySelector(target) as HTMLElement | null;
    if (!el) {
        spotlightStyle.value = { display: 'none' };
        cardStyle.value = { top: '50%', left: '50%', transform: 'translate(-50%, -50%)', maxWidth: '22rem' };
        return;
    }
    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    const rect = el.getBoundingClientRect();
    const pad = 8;
    spotlightStyle.value = {
        top: `${Math.max(0, rect.top - pad)}px`,
        left: `${Math.max(0, rect.left - pad)}px`,
        width: `${rect.width + pad * 2}px`,
        height: `${Math.max(rect.height + pad * 2, 40)}px`,
    };
    const top = Math.min(window.innerHeight - 200, rect.bottom + 12);
    const left = Math.min(window.innerWidth - 280, Math.max(12, rect.left));
    cardStyle.value = {
        top: `${top}px`,
        left: `${left}px`,
        maxWidth: 'min(22rem, calc(100vw - 24px))',
    };
}

watch([tourRunning, () => tour.stepIndex.value, () => route.fullPath], () => {
    if (tourRunning.value && portalTourBlockedForRoute(route.path)) {
        void tour.skipRunningTour();
        return;
    }
    nextTick(() => positionSpotlight());
});

onMounted(() => {
    window.addEventListener('resize', positionSpotlight);
    window.addEventListener('scroll', positionSpotlight, true);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', positionSpotlight);
    window.removeEventListener('scroll', positionSpotlight, true);
});
</script>

<template>
    <Modal
        v-model:open="tour.offerOpen"
        :title="t('portal.tour.offer_title')"
        size="sm"
    >
        <p class="portal-tour-offer__body">{{ t('portal.tour.offer_body') }}</p>
        <div class="portal-tour-offer__actions">
            <Button variant="ghost" @click="tour.dismissOffer()">{{ t('portal.tour.skip') }}</Button>
            <Button variant="primary" @click="tour.startTour()">{{ t('portal.tour.start') }}</Button>
        </div>
    </Modal>

    <Teleport to="body">
        <div
            v-if="tourRunning && tour.currentStep"
            class="portal-tour"
            role="dialog"
            :aria-label="t('portal.tour.active_label')"
        >
            <div class="portal-tour__backdrop" @click="tour.skipRunningTour()" />
            <div class="portal-tour__spotlight" :style="spotlightStyle" />
            <div class="portal-tour__card" :style="cardStyle">
                <p class="portal-tour__step">
                    {{ t('portal.tour.step_counter', { current: tour.stepIndex + 1, total: tour.steps.length }) }}
                </p>
                <h3>{{ tour.currentStep.title }}</h3>
                <p>{{ tour.currentStep.body }}</p>
                <div class="portal-tour__actions">
                    <Button variant="ghost" size="sm" @click="tour.skipRunningTour()">{{ t('portal.tour.skip') }}</Button>
                    <div class="portal-tour__nav">
                        <Button
                            variant="secondary"
                            size="sm"
                            :disabled="tour.stepIndex === 0"
                            @click="tour.prevStep()"
                        >
                            {{ t('portal.tour.back') }}
                        </Button>
                        <Button variant="primary" size="sm" @click="tour.nextStep()">
                            {{ tour.stepIndex >= tour.steps.length - 1 ? t('portal.tour.finish') : t('portal.tour.next') }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
