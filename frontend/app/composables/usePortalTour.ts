import type { ProductTourOffer, ProductTourStep } from '~/types/support-api';
import { PORTAL_TOUR_STEP_TARGETS } from '~/types/support-api';
import { unwrapResult, type ApiEnvelope } from '~/composables/useSupportApi';

export function portalTourBlockedForRoute(path: string): boolean {
    if (path.startsWith('/auth/register')) return true;
    if (/^\/portal\/cvs\/\d+\/edit/.test(path)) return true;
    if (/^\/portal\/cover-letters\/\d+\/edit/.test(path)) return true;
    return false;
}

const PENDING_AFTER_CV_KEY = 'cv.portal.tour.pending_after_cv';
const LAST_TOUR_KEY = 'cv.portal.tour.last_key';

function readPendingAfterCv(): boolean {
    if (!import.meta.client) return false;
    try {
        return window.sessionStorage.getItem(PENDING_AFTER_CV_KEY) === '1';
    } catch {
        return false;
    }
}

function writePendingAfterCv(value: boolean) {
    if (!import.meta.client) return;
    try {
        if (value) window.sessionStorage.setItem(PENDING_AFTER_CV_KEY, '1');
        else window.sessionStorage.removeItem(PENDING_AFTER_CV_KEY);
    } catch { /* ignore */ }
}

function rememberTourKey(key: string) {
    if (!import.meta.client || !key) return;
    try {
        window.sessionStorage.setItem(LAST_TOUR_KEY, key);
    } catch { /* ignore */ }
}

function readLastTourKey(): string | null {
    if (!import.meta.client) return null;
    try {
        return window.sessionStorage.getItem(LAST_TOUR_KEY);
    } catch {
        return null;
    }
}

export const usePortalTour = () => {
    const api = useApi();
    const toast = useToast();
    const { t } = useI18n();

    const offerOpen = useState('portal.tour.offerOpen', () => false);
    const activeTour = useState<ProductTourOffer | null>('portal.tour.activeTour', () => null);
    const stepIndex = useState('portal.tour.stepIndex', () => 0);
    const offeredThisSession = useState('portal.tour.offeredSession', () => false);
    const pendingAfterCv = useState('portal.tour.pendingAfterCv', () => readPendingAfterCv());

    const steps = computed(() => activeTour.value?.steps ?? []);
    const currentStep = computed<ProductTourStep | null>(() => steps.value[stepIndex.value] ?? null);

    function stepTarget(index: number): string {
        return PORTAL_TOUR_STEP_TARGETS[index] ?? PORTAL_TOUR_STEP_TARGETS[0]!;
    }

    async function fetchOffer(): Promise<ProductTourOffer[]> {
        try {
            const res = await api<ApiEnvelope<{ tours: ProductTourOffer[] }>>('/portal/tours/offer');
            const payload = unwrapResult(res);
            return payload?.tours ?? [];
        } catch {
            return [];
        }
    }

    async function completeTour(key: string) {
        try {
            await api(`/portal/tours/${encodeURIComponent(key)}/complete`, { method: 'POST' });
        } catch (e: any) {
            toast.error(e?.data?.message || t('support.toast.request_failed'));
        }
    }

    async function dismissTour(key: string) {
        try {
            await api(`/portal/tours/${encodeURIComponent(key)}/dismiss`, { method: 'POST' });
        } catch (e: any) {
            toast.error(e?.data?.message || t('support.toast.request_failed'));
        }
    }

    async function resetTour(key: string): Promise<boolean> {
        try {
            const res = await api<ApiEnvelope<{ key: string; reset: boolean }>>(
                `/portal/tours/${encodeURIComponent(key)}/reset`,
                { method: 'POST' },
            );
            const payload = unwrapResult(res);
            return payload?.reset === true;
        } catch (e: any) {
            toast.error(e?.data?.message || t('support.toast.request_failed'));
            return false;
        }
    }

    function openOfferModal(tour: ProductTourOffer) {
        rememberTourKey(tour.key);
        activeTour.value = tour;
        offerOpen.value = true;
    }

    function dismissOffer() {
        offerOpen.value = false;
        const key = activeTour.value?.key;
        if (key) void dismissTour(key);
        activeTour.value = null;
    }

    function startTour() {
        offerOpen.value = false;
        stepIndex.value = 0;
        if (!activeTour.value) return;
    }

    function isTourRunning() {
        return !!activeTour.value && !offerOpen.value;
    }

    async function finishTour() {
        const key = activeTour.value?.key;
        activeTour.value = null;
        stepIndex.value = 0;
        if (key) await completeTour(key);
    }

    async function skipRunningTour() {
        const key = activeTour.value?.key;
        activeTour.value = null;
        stepIndex.value = 0;
        if (key) await dismissTour(key);
    }

    function nextStep() {
        if (!activeTour.value) return;
        if (stepIndex.value >= steps.value.length - 1) {
            void finishTour();
            return;
        }
        stepIndex.value += 1;
    }

    function prevStep() {
        stepIndex.value = Math.max(0, stepIndex.value - 1);
    }

    async function tryOfferTour(path: string, context: 'visit' | 'dashboard') {
        if (!import.meta.client || portalTourBlockedForRoute(path) || offerOpen.value) return;

        if (context === 'dashboard') {
            if (!pendingAfterCv.value) return;
            writePendingAfterCv(false);
            pendingAfterCv.value = false;
        } else if (offeredThisSession.value) {
            return;
        }

        const tours = await fetchOffer();
        const tour = tours[0];
        if (!tour?.steps?.length) return;

        offeredThisSession.value = true;
        openOfferModal(tour);
    }

    /** After the user saves a CV, allow one dashboard re-offer if the API still has a tour. */
    function noteFirstCvSaved() {
        writePendingAfterCv(true);
        pendingAfterCv.value = true;
    }

    /**
     * Help → replay: clear server progress, then show the offer modal if the API returns a tour.
     */
    async function replayTourFromHelp() {
        if (portalTourBlockedForRoute(useRoute().path)) return;

        let key = readLastTourKey();
        const existing = await fetchOffer();
        if (existing[0]?.key) {
            key = existing[0].key;
            openOfferModal(existing[0]);
            return;
        }

        if (!key) {
            toast.error(t('portal.help.replay_tour_unavailable'));
            return;
        }

        const resetOk = await resetTour(key);
        if (!resetOk) return;

        const tours = await fetchOffer();
        const tour = tours[0];
        if (!tour?.steps?.length) {
            toast.error(t('portal.help.replay_tour_unavailable'));
            return;
        }
        openOfferModal(tour);
    }

    return {
        offerOpen,
        activeTour,
        stepIndex,
        steps,
        currentStep,
        stepTarget,
        isTourRunning,
        fetchOffer,
        tryOfferTour,
        dismissOffer,
        startTour,
        finishTour,
        skipRunningTour,
        nextStep,
        prevStep,
        noteFirstCvSaved,
        replayTourFromHelp,
        resetTour,
    };
};
