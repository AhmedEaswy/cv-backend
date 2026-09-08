/**
 * useApi — thin wrapper around $fetch that always points at the Laravel
 * API and forwards auth on both server and client:
 *
 *   - On the server, forwards the incoming Cookie header so Sanctum
 *     sessions (set during the same request) are visible.
 *   - On the client, attaches the Authorization: Bearer <token> header
 *     if a Sanctum token is stored in localStorage. The token comes
 *     from /auth/login's response.data.token and is cleared on logout
 *     or when /auth/me (or any call) returns 401.
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
            const headers = new Headers(options.headers as HeadersInit | undefined);
            headers.set('Accept', 'application/json');

            const locale = useNuxtApp().$i18n?.locale?.value;
            if (locale) {
                headers.set('Accept-Language', locale);
            }

            // Server: forward the cookie so /auth/me works during SSR
            if (import.meta.server) {
                const incoming = useRequestHeaders(['cookie']);
                if (incoming.cookie) {
                    headers.set('cookie', incoming.cookie);
                }
            } else {
                // Client: bearer token from localStorage
                const token = getToken();
                if (token) {
                    headers.set('Authorization', `Bearer ${token}`);
                }
            }

            // For FormData uploads let the browser set Content-Type
            if (options.body instanceof FormData) {
                headers.delete('Content-Type');
            }

            options.headers = headers;
        },

        onResponseError({ response }) {
            // Invalid / expired token: drop it so we don't keep auto-retrying.
            if (response.status === 401 && import.meta.client) {
                setToken(null);
                const checked = useState<boolean>('auth.checked', () => false);
                const user = useState<AuthUser | null>('auth.user', () => null);
                user.value = null;
                checked.value = true;
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

// Local type alias so onResponseError can clear auth state without circular imports.
interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}
