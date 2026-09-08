/**
 * useAuth — landing-page auth state. Resolves once per session (deduped),
 * caches via useState, and does not re-hit /auth/me after a known guest
 * or after an invalid token was cleared.
 */
export interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}

type AuthPending = Promise<void> | null;

export const useAuth = async () => {
    const user = useState<AuthUser | null>('auth.user', () => null);
    const checked = useState<boolean>('auth.checked', () => false);
    const pending = useState<AuthPending>('auth.pending', () => null);

    if (!checked.value) {
        if (!pending.value) {
            pending.value = resolveAuth(user, checked);
        }
        try {
            await pending.value;
        } finally {
            pending.value = null;
        }
    }

    return { user, checked };
};

async function resolveAuth(
    user: Ref<AuthUser | null>,
    checked: Ref<boolean>,
): Promise<void> {
    const api = useApi();

    // Client guests with no bearer token: skip /me entirely.
    if (import.meta.client && !api.getToken()) {
        user.value = null;
        checked.value = true;
        return;
    }

    // SSR with no cookie / authorization: skip /me.
    if (import.meta.server) {
        const headers = useRequestHeaders(['cookie', 'authorization']);
        const hasSession = Boolean(headers.cookie || headers.authorization);
        if (!hasSession) {
            user.value = null;
            checked.value = true;
            return;
        }
    }

    try {
        const res = await api<{ result: { user: AuthUser } }>('/auth/me');
        user.value = res.result?.user ?? null;
        if (!user.value && import.meta.client) {
            api.setToken(null);
        }
    } catch {
        if (import.meta.client) {
            api.setToken(null);
        }
        user.value = null;
    } finally {
        checked.value = true;
    }
}
