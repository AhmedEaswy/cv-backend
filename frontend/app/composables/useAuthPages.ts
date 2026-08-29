/**
 * useAuthPages — central form-submit + error-state helpers used by the
 * auth pages (login, register, forgot, reset). Wraps useApi() with
 * field-level error extraction and global toast feedback.
 */
export interface AuthFieldErrors { [key: string]: string[] }

export const useAuthPages = () => {
    const api = useApi();
    const toast = useToast();
    const errors = ref<AuthFieldErrors>({});
    const generalError = ref<string | null>(null);
    const loading = ref(false);

    function clearErrors() {
        errors.value = {};
        generalError.value = null;
    }

    function extractError(err: any): { general: string | null; fields: AuthFieldErrors } {
        const data = err?.data ?? err?.response?._data;
        if (!data) {
            return { general: err?.statusMessage || err?.message || null, fields: {} };
        }
        return {
            general: data.message ?? null,
            fields: (data.errors as AuthFieldErrors) ?? {},
        };
    }

    async function submit<T = unknown>(
        url: string,
        body: Record<string, any>,
        successMessage?: string,
    ): Promise<{ ok: true; data: T } | { ok: false }> {
        clearErrors();
        loading.value = true;
        try {
            const res = await api<{ data: T }>(url, { method: 'POST', body });
            if (successMessage) toast.success(successMessage);
            return { ok: true, data: res.data };
        } catch (err: any) {
            const { general, fields } = extractError(err);
            errors.value = fields;
            generalError.value = general;
            if (general) toast.error(general);
            return { ok: false };
        } finally {
            loading.value = false;
        }
    }

    function fieldError(name: string): string | null {
        const arr = errors.value?.[name];
        return arr && arr.length ? arr[0] : null;
    }

    return { submit, loading, errors, generalError, fieldError, clearErrors };
};

/**
 * useAuthSession — wraps the current auth state, login, register, etc.
 * On login/register we store the bearer token returned by the API
 * and refresh /auth/me. On the server (SSR) we forward the cookie.
 */
export const useAuthSession = () => {
    const user = useState<any>('auth.user', () => null);
    const api = useApi();

    async function refresh() {
        try {
            const res = await api<{ data: any }>('/auth/me');
            user.value = res.data ?? null;
        } catch {
            user.value = null;
        }
        return user.value;
    }

    async function login(payload: { email: string; password: string; remember?: boolean }) {
        const auth = useAuthPages();
        const result = await auth.submit<{ user: any; token: string }>('/auth/login', payload);
        if (result.ok && result.data?.token) {
            api.setToken(result.data.token);
            await refresh();
            if (import.meta.client) window.location.href = '/portal';
        }
        return result;
    }

    async function register(payload: { name: string; email: string; password: string; password_confirmation: string }) {
        const auth = useAuthPages();
        const result = await auth.submit<{ user: any; token: string }>('/auth/register', payload);
        if (result.ok && result.data?.token) {
            api.setToken(result.data.token);
            await refresh();
            if (import.meta.client) window.location.href = '/portal';
        }
        return result;
    }

    async function forgot(payload: { email: string }) {
        const auth = useAuthPages();
        return auth.submit('/auth/forgot-password', payload);
    }

    async function reset(payload: { token: string; email: string; password: string; password_confirmation: string }) {
        const auth = useAuthPages();
        return auth.submit('/auth/reset-password', payload, 'auth.password_reset_success');
    }

    async function logout() {
        try {
            await api('/auth/logout', { method: 'POST' });
        } catch { /* ignore */ }
        api.setToken(null);
        user.value = null;
        if (import.meta.client) window.location.href = '/';
    }

    return { user, refresh, login, register, forgot, reset, logout };
};
