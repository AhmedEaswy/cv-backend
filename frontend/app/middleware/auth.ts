/**
 * Auth middleware — gates the /portal/* and other authenticated routes.
 * Bearer token lives in localStorage (client only). Invalid tokens are
 * cleared by useApi / refresh and are not retried until the user logs in again.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    if (import.meta.server) return;

    const { user, checked, refresh } = useAuthSession();
    const api = useApi();

    if (!api.getToken()) {
        user.value = null;
        checked.value = true;
        return navigateTo({
            path: '/auth/login',
            query: { redirect: to.fullPath },
        });
    }

    if (!checked.value || user.value === null) {
        await refresh();
    }

    if (!user.value) {
        return navigateTo({
            path: '/auth/login',
            query: { redirect: to.fullPath },
        });
    }
});
