/**
 * Offer product tours from GET /portal/tours/offer (not on CV editor or register).
 */
export default defineNuxtPlugin(() => {
    const router = useRouter();
    const tour = usePortalTour();

    router.afterEach((to) => {
        if (!to.path.startsWith('/portal')) return;
        if (to.path === '/portal') {
            void tour.tryOfferTour(to.path, 'dashboard');
            void tour.tryOfferTour(to.path, 'visit');
            return;
        }
        void tour.tryOfferTour(to.path, 'visit');
    });

    onNuxtReady(() => {
        const path = router.currentRoute.value.path;
        if (path === '/portal') {
            void tour.tryOfferTour(path, 'dashboard');
            void tour.tryOfferTour(path, 'visit');
        } else if (path.startsWith('/portal')) {
            void tour.tryOfferTour(path, 'visit');
        }
    });
});
