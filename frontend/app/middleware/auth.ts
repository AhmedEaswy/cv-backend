/**
 * Auth middleware — gates the /portal/* and other authenticated routes.
 * On the server, forwards the cookie so /auth/me can run during SSR.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    // Bearer token is stored in localStorage — only available in the browser.
    if (import.meta.server) return;

    const { user, refresh } = useAuthSession();

    if (user.value === null) {
        await refresh();
    }

    if (!user.value) {
        return navigateTo({
            path: '/auth/login',
            query: { redirect: to.fullPath },
        });
    }
});
