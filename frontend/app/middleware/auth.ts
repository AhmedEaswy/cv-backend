/**
 * Auth middleware — gates the /portal/* and other authenticated routes.
 * On the server, forwards the cookie so /auth/me can run during SSR.
 */
export default defineNuxtRouteMiddleware(async (to) => {
    const { user, refresh } = useAuthSession();

    // Re-fetch on every navigation so we always reflect the latest session
    // (cheap when the cookie is set — just a single /auth/me round trip).
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
