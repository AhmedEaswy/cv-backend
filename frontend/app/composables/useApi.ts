/**
 * useApi — thin wrapper around $fetch that always points at the Laravel
 * API and forwards auth on both server and client:
 *
 *   - On the server, forwards the incoming Cookie header so Sanctum
 *     sessions (set during the same request) are visible.
 *   - On the client, attaches the Authorization: Bearer <token> header
 *     if a Sanctum token is stored in localStorage. The token comes
 *     from /auth/login's response.data.token and is cleared on logout.
 */
const TOKEN_KEY = 'cv.auth.token';

export const useApi = () => {
    const config = useRuntimeConfig();
    const base = (config.public.laravelUrl as string).replace(/\/+$/, '');
    const prefix = (config.public.apiPrefix as string).replace(/\/+$/, '');

    function getToken(): string | null {
        if (!import.meta.client) return null;
        try { return window.localStorage.getItem(TOKEN_KEY); } catch { return null; }
    }

    function setToken(token: string | null) {
        if (!import.meta.client) return;
        try {
            if (token) window.localStorage.setItem(TOKEN_KEY, token);
            else window.localStorage.removeItem(TOKEN_KEY);
        } catch { /* ignore */ }
    }

    const api = $fetch.create({
        baseURL: `${base}${prefix}`,

        onRequest({ options }) {
            const locale = useNuxtApp().$i18n?.locale?.value;
            if (locale) {
                options.headers = { ...(options.headers || {}), 'Accept-Language': locale };
            }

            // Server: forward the cookie so /auth/me works during SSR
            if (import.meta.server) {
                const headers = useRequestHeaders(['cookie']);
                if (headers.cookie) {
                    options.headers = { ...(options.headers || {}), cookie: headers.cookie };
                }
                return;
            }
            // Client: bearer token from localStorage
            const token = getToken();
            if (token) {
                options.headers = { ...(options.headers || {}), Authorization: `Bearer ${token}` };
            }
            // For FormData uploads let the browser set Content-Type
            if (options.body instanceof FormData) {
                delete (options.headers as any)['Content-Type'];
            }
        },
    });

    return Object.assign(api, { setToken, getToken });
};

/**
 * useRawFetch — used by useAuth* to call the API without auto-wiring
 * a token (the auth endpoints set the token on success). The
 * signature is identical to $fetch.
 */
export const useRawFetch = () => $fetch;
